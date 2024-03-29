<?php
require_once 'vendor/autoload.php';
require_once "./random_string.php";

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

$connectionString = "DefaultEndpointsProtocol=https;AccountName=myexampleapp;AccountKey=zeniCmdQxnNBMn+GJJhlZLgUxFrl3hLnisNngUuj9s+b19UG7F5L/hjy2zWp4k1oaeJm9tklKqA5YnHiBl3RAw==";

// Create blob client.


 ?>

<html>
 <head>
   <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
   <meta charset="utf-8">
       <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

 <Title>Registration Form</Title>
 <style type="text/css">
 	body { background-color: #fff; border-top: solid 10px #000;
 	    color: #333; font-size: .85em; margin: 20; padding: 20;
 	    font-family: "Segoe UI", Verdana, Helvetica, Sans-Serif;
 	}
 	h1, h2, h3,{ color: #000; margin-bottom: 0; padding-bottom: 0; }
 	h1 { font-size: 2em; }
 	h2 { font-size: 1.75em; }
 	h3 { font-size: 1.2em; }
 	table { margin-top: 0.75em; }
 	th { font-size: 1.2em; text-align: left; border: none; padding-left: 0; }
 	td { padding: 0.25em 2em 0.25em 0em; border: 0 none; }
 </style>
 </head>
 <body>
 <h1>Register here!</h1>
 <p>Fill in your name and email address, then click <strong>Submit</strong> to register.</p>
 <form method="post" action="index.php" enctype="multipart/form-data" >
       Gambar  <input type="file" name="gambar"  accept=".jpeg,.jpg,.png"  id="gambar"/></br></br>

       <input type="submit" class="btn btn-primary" name="submit" value="Submit" />
       <input type="submit" class="btn btn-secondary" name="load_data" value="Load Data" />
 </form>
 <?php
  $host = "tcp:gdocha.database.windows.net";
    $user = "gdocha";
    $pass = "Ananda66";
    $db = "mymail";

    try {
        $conn = new PDO("sqlsrv:server = $host; Database = $db", $user, $pass);
        $conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
    } catch(Exception $e) {
        echo "Failed: " . $e;
    }

    if (isset($_POST['submit'])) {
      $createContainerOptions = new CreateContainerOptions();
      $createContainerOptions->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);

      $createContainerOptions->addMetaData("key1", "value1");
      $createContainerOptions->addMetaData("key2", "value2");

      $file = $_FILES['gambar']['name'];
      $_imgExtension = end((explode(".",$file)));
      date_default_timezone_get('Asia/Kuala_Lumpur');
      $time = date('Ymdhis');

      $gambar = $time.".".$_imgExtension;
      $sizeFile = $_FILES['gambar']['size'];
      $typeFile = $_FILES['gambar']['type'];
      $fileToUpload = $_FILES['gambar']['tmp_name'];
      $containerName = "blockblobs".generateRandomString();
      $get_name = $containerName;

        try {
          $blobClient = BlobRestProxy::createBlobService($connectionString);
          // Create container.
          $blobClient->createContainer($get_name, $createContainerOptions);

          // Getting local file so that we can upload it to Azure
         $myfile = fopen($fileToUpload, "r") or die("Unable to open file!");
          fclose($myfile);

          # Mengunggah file sebagai block blob
          echo "Uploading BlockBlob: ".PHP_EOL;
          echo $fileToUpload;
          echo "<br />";

          $content = fopen($fileToUpload, "r");

          //Upload blob
          $blobClient->createBlockBlob($get_name, $gambar, $content);

          // List blobs.
          $listBlobsOptions = new ListBlobsOptions();
          $listBlobsOptions->setPrefix("HelloWorld");
          $url="";

          do{
              $result = $blobClient->listBlobs($get_name, $listBlobsOptions);
              foreach ($result->getBlobs() as $blob)
              {
                  echo $blob->getName().": ".$blob->getUrl()."<br />";
                  $url = $blob->getUrl();

              }

              $listBlobsOptions->setContinuationToken($result->getContinuationToken());
          } while($result->getContinuationToken());
          echo "<br />";
          //https://myexampleapp.blob.core.windows.net/blockblobsbqgwnp/Royal-Gems-Golf-City-003.jpg
        $url = 'https://myexampleapp.blob.core.windows.net/'.$containerName.'/'.$gambar.'';
          $sql_insert = "INSERT INTO tbl_vision2(gambar) VALUES (?);";
          $stmt = $conn->prepare($sql_insert);
           $stmt->bindValue(1, $url);
          $stmt->execute();

        } catch(Exception $e) {
            echo "Failed: " . $e;
        }

        catch(ServiceException $e){
            // Handle exception based on error codes and messages.
            // Error codes and messages are here:
            // http://msdn.microsoft.com/library/azure/dd179439.aspx
            $code = $e->getCode();
            $error_message = $e->getMessage();
            echo $code.": ".$error_message."<br />";
        }
        catch(InvalidArgumentTypeException $e){
            // Handle exception based on error codes and messages.
            // Error codes and messages are here:
            // http://msdn.microsoft.com/library/azure/dd179439.aspx
            $code = $e->getCode();
            $error_message = $e->getMessage();
            echo $code.": ".$error_message."<br />";
        }

        echo "<h3>Your're registered!</h3>";
    } else if (isset($_POST['load_data'])) {
        try {
            $sql_select = "SELECT * FROM tbl_vision2";
            $stmt = $conn->query($sql_select);
            $registrants = $stmt->fetchAll();
            if(count($registrants) > 0) {
                echo "<h2>People who are registered:</h2>";
                echo "<table>";
                echo "<tr><th>Gambar</th>";
                echo "<th>Action</th></tr>";
                foreach($registrants as $registrant) {
                    echo "<tr><td>".$registrant['gambar']."</td>";
                    echo "<td><a href='vision.php?url=".$registrant['gambar']."' class='btn btn-primary' >Analisa</a>  </td></tr>";
                }
                echo "</table>";
            } else {
                echo "<h3>No one is currently registered.</h3>";
            }
        } catch(Exception $e) {
            echo "Failed: " . $e;
        }
    }
 ?>

 </body>
 </html>
