<?php require_once 'dbConnect.php'; ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
        <title>Document</title>
        <style>
            body{
                margin: 0;
                padding: 50px;
            }
            </style>
    </head>
    <body onload="startTime()">
        <h1><?php print ucwords(str_replace("_", " ", $group_table)); ?></h1>
        <h2 id="txt"></h2>
        <form>
            <input type="text" class="form-control" name="search_text" id="search_text" id="exampleFormControlInput1" placeholder="Search..." autofocus autocomplete="off" />
        </form>
        <br>
        <div id="result_list"></div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="userDisplay.js"></script>
    </body>
</html>