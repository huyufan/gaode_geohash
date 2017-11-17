<!DOCTYPE html>
<htm>
    <head>
        <meta charset="UTF-8">
        <title>录入经纬度</title>
        <link rel="shortcut icon" href="http://zptest-static.heebird.com/favicon.ico?v=kg1fd9gf3mhg2f22" type="image/x-icon" />
        <link href="css/common.css" rel="stylesheet" type="text/css"/>
        <style type="text/css">
            #container {width:100%; height: 580px; margin-top:30px; } 
            .panel {
                background-color: #ddf;
                color: #333;
                border: 1px solid silver;
                box-shadow: 3px 4px 3px 0px silver;
                position: absolute;
                top: 40px;
                right: 10px;
                border-radius: 5px;
                overflow: hidden;
                line-height: 20px;
            }
            #input{
                width: 250px;
                height: 25px;
                border: 0;
            }
        </style>
    </head>
    <body>
        <div>
            <?php
            if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
                if (isset($_POST["lnt"]) && isset($_POST["long"]) && isset($_POST["name"])) {
                    include "geo.php";
                    $link = mysqli_connect("192.168.1.14", "test", "111111", "test", 3306);
                    if (mysqli_connect_errno()) {
                        printf("Connect failed: %s\n", mysqli_connect_error());
                        exit();
                    }
                    mysqli_query($link, "set names utf8");
                    $geo = new geo();
                    $lat = $_POST["lnt"];
                    $long = $_POST["long"];
                    $name = $_POST["name"];
                    $geo_re = $geo->encode($lat, $long);
                    $pis_geo = substr($geo_re, 0, 6);
                    $sql = "insert into shop (`name`,`lat`,`long`,`geo`,`pis_geo`) values  ('$name','$lat','$long','$geo_re','$pis_geo')";
                    $result = mysqli_query($link, $sql);
                    $link->close();
                    if ($result) {
                        echo "添加成功";
                    } else {
                        echo "添加失败" . mysqli_error($link);
                    }
                }
            }
            ?>
            <div style=" margin-top:20px;">
                <form action="" method="post">
                    <span>添加记录</span>&nbsp;&nbsp;&nbsp;
                    <span>商家名称</span><input type="text" name="name" id="name"/>&nbsp;&nbsp;&nbsp;
                    <span>商家坐标(经纬度)</span> <input type="text" name="lnt"  id="lnt"/> <span>(纬度)-</span><input type="text" name="long" id="long"/> <span>(经度)</span>
                    &nbsp;&nbsp;<input type="submit" value="添加记录">
                </form>
            </div>
            <div id="container" tabindex="0"></div>
            <div class ='panel'>
                <input  id = 'input' value = '点击地图显示地址/输入地址显示位置' onfocus = 'this.value = ""'></input>
                <div id = 'message'></div>
            </div>
        </div>


    <script src="http://cache.amap.com/lbs/static/es5.min.js"></script>
    <script src="http://webapi.amap.com/maps?v=1.4.1&key=d819fee426796fa427db1d4d727bffd8"></script>
    <script type="text/javascript" src="http://cache.amap.com/lbs/static/addToolbar.js"></script>
        <script type="text/javascript">
                    var map = new AMap.Map('container', {
                        resizeEnable: true,
                        zoom: 13,
                        center: [116.39, 39.9]
                    });
                    AMap.plugin('AMap.Geocoder', function () {
                        var geocoder = new AMap.Geocoder({
                            city: "010"//城市，默认：“全国”
                        });
                        var marker = new AMap.Marker({
                            map: map,
                            bubble: true
                        })
                        var input = document.getElementById('input');
                        var lnt = document.getElementById('lnt');
                        var long = document.getElementById('long');
                        var name = document.getElementById('name');
                        var message = document.getElementById('message');
                        map.on('click', function (e) {
                            console.log(e.lnglat);
                            marker.setPosition(e.lnglat);
                            geocoder.getAddress(e.lnglat, function (status, result) {
                                if (status == 'complete') {
                                    input.value = result.regeocode.formattedAddress;
                                    lnt.value = e.lnglat.lat;
                                    long.value = e.lnglat.lng;
                                    name.value = result.regeocode.formattedAddress;
                                    message.innerHTML = ''
                                } else {
                                    message.innerHTML = '无法获取地址'
                                }
                            })
                        })

                        input.onchange = function (e) {
                            var address = input.value;
                            geocoder.getLocation(address, function (status, result) {
                                if (status == 'complete' && result.geocodes.length) {
                                    var lnt = document.getElementById('lnt');
                                    var long = document.getElementById('long');
                                    var name = document.getElementById('name');
                                    lnt.value = result.geocodes[0].location.lat;
                                    long.value = result.geocodes[0].location.lng;
                                    name.value = address;
                                    console.log(result.geocodes[0].location.lat);
                                    marker.setPosition(result.geocodes[0].location);
                                    map.setCenter(marker.getPosition())
                                    message.innerHTML = ''
                                } else {
                                    message.innerHTML = '无法获取位置'
                                }
                            })
                        }

                    });
        </script>
    </body>

</htm>