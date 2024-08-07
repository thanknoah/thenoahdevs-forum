<?php
   class essential
   {   
       function logout()
       {
           setcookie('token', '', time() - 3600, '/');
       }
       
       function displayPost($conn)
       {
           $sql = "SELECT * FROM posts ORDER BY id DESC LIMIT 5;";
           $query = $conn->query($sql);
           
           if ($query->num_rows == 0)
           {
               echo "<h2 style='color:Tomato'>No new posts!</h2>";
           } else
           {
               while ($row = $query->fetch_assoc())
               {
                   $author = $row['author'];
                   $title = $row['title'];
                   $desc = $row['description'];
                   $time = $row['timestamp'];
                   $id = $row['id'];
                   $link = $row['link'];

                   $numOfComments = $conn->query("SELECT * FROM comments WHERE id=${id}")->num_rows;
                   $numOfLikes = $conn->query("SELECT * FROM likes WHERE id=${id}")->num_rows;
                   $numOfFollowers = $conn->query("SELECT * FROM followers WHERE following='$author'")->num_rows;
                   $userQuery = $conn->query("SELECT * FROM users WHERE username='${author}'");
                   
                   if ($author == "thanknoah") $numOfFollowers = $numOfFollowers + 10;
                   
                   
                   $profilePictureLink = "";
                   $descriptionOfUser = "";
                   $userId = "";
                   
                   while ($row = $userQuery->fetch_assoc())
                   {
                       $userId = $row["id"];
                       $profilePictureLink = $row["pfp"];
                       $descriptionOfUser = $row["description"];
                   }
                   
                   if ($descriptionOfUser == "") $descriptionOfUser = "No description.";
                   
                   if ($profilePictureLink == "" || $profilePictureLink == null)
                   {
                       echo "<div class='grid-container'>
                          <div class='grid-item' onclick='openUserProfile(`https://thenoahdevs.com/forum/profile.php?v=${author}`)'>
                             <img id='notLoadedin' src='https://thenoahdevs.com/forum/profile_pictures/default.jpg' width='200' height='200' style='margin-right: 5px; border-radius: 50%;  position: absoloute; left: 50px;bottom: 5px;'>
                             <h3>Author: ${author}</h3>
                             <h3>Followers: ${numOfFollowers}</h3>
                             <h3>Bio: ${descriptionOfUser}</h3>
                           </div>
                           <div class='grid-item' onclick='openUserProfile(`${link}`)'>
                             <h1><a href=${link} style='color: #FF7276;  
                             text-shadow: 0px 0px 15px currentcolor; '>Title: ${title}</a></h1>
                             <h2 style='color: orange;  text-shadow: 0px 0px 15px currentcolor;'>Description: ${desc}</h2>
                             <h2>Posted at: ${time}</h2>
                             <h3>Comments: $numOfComments,   Likes: $numOfLikes</h3>
                           </div>
                        </div>";
                   } else {
                       echo "<div class='grid-container'>
                          <div class='grid-item' onclick='openUserProfile(`https://thenoahdevs.com/forum/profile.php?v=${author}`)'>
                             <img id='${profilePictureLink}' src='https://thenoahdevs.com/forum/${profilePictureLink}' width='200' height='200' style='margin-right: 5px; border-radius: 50%; position: absoloute; left: 50px;bottom: 5px;'>
                             <br>
                             <h3>Author: ${author}</h3>
                             <h3>Followers: ${numOfFollowers}</h3>
                             <h3>Bio: ${descriptionOfUser}</h3>
                           </div>
                           <div class='grid-item' onclick='openUserProfile(`${link}`)'>
                             <h1><a href=${link} style='color: #FF7276;  text-shadow: 0px 0px 15px currentcolor;'>Title: ${title}</a></h1>
                             <h2 style='color: orange;  text-shadow: 0px 0px 15px currentcolor;'>Description: ${desc}</h2>
                             <h2>Posted at: ${time}</h2>
                             <h3>Comments: $numOfComments,   Likes: $numOfLikes</h3>
                           </div>
                        </div>";
                   }


               }
           }
       }
       
       function displayRole($conn)
       {
           $token = $_COOKIE['token'];
           $sql = "SELECT * FROM users WHERE hash_identifier = '${token}';";
           $query = $conn->query($sql);
           
           while ($row = $query->fetch_assoc())
           {
               return $row['role'];
           }
       }
       
       function displayName($conn)
       {
           $token = $_COOKIE['token'];
           $sql = "SELECT * FROM users WHERE hash_identifier = '${token}';";
           $query = $conn->query($sql);
           
           while ($row = $query->fetch_assoc())
           {
               return $row['username'];
           }
       }
       
       function verifyToken($conn)
       {
          $token = substr($_SERVER['REQUEST_URI'], 22, strlen($_SERVER['REQUEST_URI']));
          $currentCookie = $_COOKIE["token"];
          $verifyNewToken = $conn->query("SELECT * FROM users WHERE hash_identifier = '${token}';")->num_rows;
          $verifyExistingToken = $conn->query("SELECT * FROM users WHERE hash_identifier = '${currentCookie}';")->num_rows;
          
          if ($verifyNewToken == 1)
          {
              setcookie("token", $token, time() + (10 * 365 * 24 * 60 * 60), "/");
              header("Location: https://thenoahdevs.com/forum/home.php");
          } else if ($verifyExistingToken == 1)
          {
              return;
          } else
          {
              header("Location: https://thenoahdevs.com/forum/register.php");
              setcookie('token', '', time() - 3600, '/');
          }
       }
   }
   // Info
   $db_username = "u974149784_user_db";
   $db_user = "u974149784_users";
   $db_password = "O4xq:Avo&";
   $db_host = "localhost";
   
   // Testing because class doesnt work
   $conn = new mysqli($db_host, $db_user, $db_password, $db_username);
   if ($conn->connect_errno) {
      echo $conn->connect_error;
      exit();
   }
   
   // Class Initialization
   $main = new essential();
   $main->verifyToken($conn);
   $username = $main->displayName($conn);
   $role = $main->displayRole($conn);
   
   // Logout Setup
   if ($_SERVER["REQUEST_METHOD"] == "POST") 
   {
      $main->logout();
   }  
   
   // Echo
   echo '<ul>
           <li><a class="active" href="#home">Home</a></li><li><a href="https://thenoahdevs.com/forum/post.php">Posts</a></li>
           <li><a href="#about">About</a></li>
           <li><a href="https://thenoahdevs.com/forum/chat.html">Live chat</a></li>
           <li><a href="https://thenoahdevs.com/forum/profile.php">Profile Settings</a></li>
           <li><a onclick="post()">Logout</a></li>
         </ul>';
   echo "<h1>Welcome, <div id='nameRainbow' style='text-shadow: 0px 0px 15px currentcolor;'>${username}!</div></h1>";
   echo "<h2>Your role: ${role}</h2>";
   echo "<h3>Update log: User profile UI redesigned, follow/unfollow button feature, and more! -Yours truly, Noah</h3>";
   echo "<h5 style='color: red;text-shadow: 0px 0px 15px currentcolor;'>Version 1.9</h5>";
   echo "<br><h2>Latest Posts:</h2>";
   
   // Show latest posts
   $latestPosts = $main->displayPost($conn);
?>
