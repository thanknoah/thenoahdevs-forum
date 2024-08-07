<?php
  class handleFormSubmission
  {
      public $db_username;
      public $db_user;
      public $db_password;
      public $db_host;
      public $conn;
      public $name;
      public $id;
      public $role;
      
      function __construct()
      {
         $this->db_username = "u974149784_user_db";
         $this->db_user = "u974149784_users";
         $this->db_password = "O4xq:Avo&";
         $this->db_host = "localhost";
         $this->conn = new mysqli($this->db_host, $this->db_user, $this->db_password, $this->db_username) or die('Unable to connect.');   
      }
      
      function verifyToken($token)
      {
          $query = $this->conn->query("SELECT * FROM users WHERE hash_identifier = '${token}';");
          if ($query->num_rows == 1)
          {
              while ($row = $query->fetch_assoc())
              {
                  return $row['username'];
              }
          }
          return false;
      }
      
      function setName()
      {
          if (isset($_COOKIE["token"]))
          {
              $token = $this->conn->real_escape_string(htmlspecialchars($_COOKIE["token"]));
              $sql = "SELECT * FROM users WHERE hash_identifier = '${token}';";
              $query = $this->conn->query($sql);
              $name = "";
              
              while ($row = $query->fetch_assoc())
              {
                  $name = $row['username'];
                  $role = $row['role'];
                  $id = $row['id'];
              }
              
              if ($name == "") 
              { 
                  header("Location: https://thenoahdevs.com/forum/register.php?msg=NOT_LOGGED_IN");
              }
              
              $this->name = $name;
              $this->id = $id;
              $this->role = $role;
          } else {
              header("Location: https://thenoahdevs.com/forum/register.php?msg=NOT_LOGGED_IN");
          }
      }
      
      function getName()
      {
          return $this->name;
      }
      
      function addFollower()
      {
          $token = $this->conn->real_escape_string(htmlspecialchars($_REQUEST['token']));
          $usernameToFollow = $this->conn->real_escape_string(htmlspecialchars($_REQUEST['name']));
          $username = $this->verifyToken($token);
          $duplicateFollowerCheck = $this->conn->query("SELECT * FROM followers WHERE user='${username}' AND following='${usernameToFollow}'");
          
          if (is_string($username))
          {
              if ($duplicateFollowerCheck->num_rows == 0)
              {
                  $this->conn->query("INSERT INTO followers VALUES ('${username}', '${usernameToFollow}')");
              } else
              {
                  $this->conn->query("DELETE FROM followers WHERE user='${username}' AND following='${usernameToFollow}'");
              }
          }
      }
      
      function loadProfile()
      {  
           $name = substr($_SERVER['REQUEST_URI'], 21, strlen($_SERVER['REQUEST_URI']));
           $userDetails = $this->conn->query("SELECT * FROM users WHERE username='$name'");
           
          
           if ($userDetails->num_rows == 1)
           {
              while ($row = $userDetails->fetch_assoc())
              {
                   echo '
                   <ul>
                    <li><a class="active" href="https://thenoahdevs.com/forum/home.php">Home</a></li><li><a href="https://thenoahdevs.com/forum/post.php">Posts</a></li>
                    <li><a href="#about">About</a></li>
                   <li><a href="https://thenoahdevs.com/forum/chat.html">Live chat</a></li>
                   <li><a href="https://thenoahdevs.com/forum/profile.php">Profile Settings</a></li>
                    <li><a onclick="post()">Logout</a></li>
                   </ul>';
                   
                   $pfp = $row["pfp"];
                   $user = $row["username"];
                   $desc = $row["description"];
                   $role = $row["role"];
                   $amountOfFollowers = $this->conn->query("SELECT * FROM followers WHERE following='$user'")->num_rows;
                   
                   echo "<br><img src='https://thenoahdevs.com/forum/${pfp}' style='float: left; margin-right: 15px;  border-radius: 30%;' alt='no pfp set' width='200' height='200'/><h1 style='overflow: hidden; color: #FF7276;'>${user} [${role}]</h2><h3 style='color: orange; overflow: hidden;' >Description: ${desc}</h3><h3>Followers: ${amountOfFollowers}</h3><button onClick='follow()'>Follow</button>
                       <script>
                        function getCookie(cname) {
                         let name = cname + '=';
                         let decodedCookie = decodeURIComponent(document.cookie);
                         let ca = decodedCookie.split(';');
                         for(let i = 0; i <ca.length; i++) {
                            let c = ca[i];
                            while (c.charAt(0) == ' ') {
                              c = c.substring(1);
                            }
                            if (c.indexOf(name) == 0) {
                               return c.substring(name.length, c.length);
                           }
                         }
                         return '';
                        }
        
                        let cookie = getCookie('token');
                        function follow() { 
                            fetch('https://thenoahdevs.com/forum/profile.php?token=' + cookie + '&name=${user}', {})
                      
                            setTimeout(() => {
                                location.reload();
                            }, 300);
                        }
                       </script>
                       ";
              }
           } else
           {
               echo "<h1>Invalid user, or user hasnt set profile pic yet</h1>";
           }
      }
      
      
      function loadCustomizeProfileForm()
      {
          echo '
          <ul>
          <li><a class="active" href="https://thenoahdevs.com/forum/home.php">Home</a></li><li><a href="https://thenoahdevs.com/forum/post.php">Posts</a></li>
           <li><a href="#about">About</a></li>
           <li><a href="https://thenoahdevs.com/forum/chat.html">Live chat</a></li>
           <li><a href="https://thenoahdevs.com/forum/profile.php">Profile Settings</a></li>
           <li><a onclick="post()">Logout</a></li>
         </ul>
         <h1 id="description">Enter your description/bio:</h1>
    
    <form method="post" enctype="multipart/form-data" action="https://thenoahdevs.com/forum/profile.php">
        <input type="text" id="desc" name="desc" style="padding: 10px"/>
        <h1>Profile Picture: [MUST PUT]</h1>
        <input type="file" name="fileToUpload" id="fileToUpload" style="padding: 10px; text-align: center; color: black;">
        <br>
        <br>
        <button class="button" style="vertical-align:middle"><span>Submit</span></button>
    </form>';
      }
      function uploadProfileChanges()
      {
          // Upload Image
          if ($_SERVER["REQUEST_METHOD"] == "POST")
          {
             $target_dir = "profile_pictures/";
             $target_file = $target_dir . $this->name . basename($_FILES["fileToUpload"]["name"]);
             $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
             $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
             $desc = $this->conn->real_escape_string(htmlspecialchars($_POST["desc"]));
             $output = "";
             $uploadOk = 1;
             
             $sql = "SELECT * FROM users WHERE username='$this->name'";
             $query = $this->conn->query($sql);
             
             
             if ($query->num_rows >= 1)
             {
                 while ($row = $query->fetch_assoc())
                 {
                     $pfp_link = $row["pfp"];
                     
                     if ($pfp_link) unlink($pfp_link);
                 }
             }
             
             if (file_exists($target_file)) 
             {
                echo "Sorry, file already exists.";
                $uploadOk = 0;
             } 
             
             
             if ($_FILES["fileToUpload"]["size"] > 500000) 
             {
                echo "The pfp you attempted to upload was the same as you already have.";
                $uploadOk = 0;
             }
             
             if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) 
             {
                echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                $uploadOk = 0;
             }
             
             
             if ($uploadOk == 0) 
             {
                echo "Sorry, your file was not uploaded.";
             } else 
             {
                if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) 
                {
                  echo "Profile picture has been updated.";
                  $fileDirectory =  'profile_pictures/' . $this->name . basename($_FILES["fileToUpload"]["name"]);
                  $sql = "UPDATE users SET pfp = '$fileDirectory', description = '$desc' WHERE username = '$this->name';";
                  $query = $this->conn->query($sql);
                } else 
                {
                  echo "Sorry, there was an error uploading your file.";
                }              
             }
             
         }
      }
  }
 
?>
