<?php
   header("Access-Control-Allow-Origin: https://thenoahdevs.com/");
   include 'imports.php';
   
   // Handle Uploading Profile
   $handleSubmit = new handleFormSubmission();

   // Handling Requests
   if (isset($_REQUEST["token"]) && isset($_REQUEST["name"]))
   {
       $handleSubmit->addFollower();
   }

   // Load settings or profile
   if (isset($_REQUEST["v"]))
   {
       $handleSubmit->loadProfile();
      
   } else
   {
       $handleSubmit->loadForm();
       $handleSubmit->setName();
       $handleSubmit->uploadChanges();
       $name =  $handleSubmit->getName();
   }
?>
