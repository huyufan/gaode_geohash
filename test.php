<?php

header("Content-type:text/html;charset=utf-8");
include "geo.php";
$link= mysqli_connect("192.168.1.14", "test","111111", "test", 3306);
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}
mysqli_query($link,"set names utf8");
$geo=new geo();
$lat='39.926995';
$long='116.375593';
$name='bi4';
$geo_re=$geo->encode($lat,$long);
$sql ="insert into shop (`name`,`lat`,`long`,`geo`) values  ('$name','$lat','$long','$geo_re')";
echo $sql;
var_dump(mysqli_query($link,$sql));
var_dump(mysqli_error($link));
$link->close();
var_dump($geo_re);