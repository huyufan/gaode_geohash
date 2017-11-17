<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="initial-scale=1.0, user-scalable=no, width=device-width">
        <title>地图展示</title>
        <link href="css/common.css" rel="stylesheet" type="text/css"/>
        <script src="http://webapi.amap.com/maps?v=1.4.1&key=d819fee426796fa427db1d4d727bffd8"></script>
        <style type="text/css">
            #contr {width: 100%;}
            #left {float: left;width:33%; height: 700px;} 
            #container {float: right;width:67%; height: 700px; } 
        </style>
    </head>
    <body>
        <div>
            <?php
            function spl_sq(&$itme,$key,$val){
                //$itme " pis_geo=$val or";
            }
            $act = array();
            $msg = "";
            $lat="";
            $long="";
            if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
                if (!empty($_POST["lnt"]) && !empty($_POST["long"]) && !empty($_POST["name"])) {
                    include "geo.php";
                    $link = mysqli_connect("192.168.1.14", "test", "111111", "test", 3306);
                    if (mysqli_connect_errno()) {
                        printf("Connect failed: %s\n", mysqli_connect_error());
                        exit();
                    }
                    mysqli_query($link, "set names utf8");
                    $geo = new geo();
                    //var_dump($_POST);
                    $lat = $_POST["lnt"];
                    $long = $_POST["long"];
                    $name = $_POST["name"];
                    $geo_re = $geo->encode($lat, $long);
                    $pis_geo = substr($geo_re, 0, 6);
                    $neighbors = $geo->neighbors($pis_geo);
                    $value_list="(pis_geo='$pis_geo' or ";
                   foreach($neighbors as $key=>$val){
                       $value_list .=" pis_geo='$val' or";
                   }
                   $value_list= substr($value_list, 0, -2).")"; 
                    $result = mysqli_query($link, "select name,lat,`long` from shop where $value_list");
                    if ($result->num_rows) {
                        while ($row = $result->fetch_array()) {
                            $mi = $geo->getDistance($lat, $long, $row[1], $row[2]);
                            $rows[] = array_merge($row, array('age' => $mi));
                            //array_multisort();
                            $act[] = array($row[2], $row[1]);
                            //$rows[] = $row;
                        }
                        foreach ($rows as $row_list) {
                            $ages[] = $row_list['age'];
                        }
                        array_multisort($ages, SORT_ASC, $rows);
                        $msg = "总结查询到($result->num_rows)条记录";
                    } else {
                        $msg = "没有查到($name)附近的商家";
                    }
                    $result->close();
                    $link->close();
                }else{
                    $msg="没有找到地址";
                }
            }
            $act = json_encode($act);
            ?>
            <div style="padding-left:30px; margin-top: 20px; margin-bottom: 45px;">
                <form action="" method="post" id="form">
                    <input type="hidden" name="lnt" id="lnt">
                    <input type="hidden" name="long" id="long">
                    <span>输入当前地址</span> <input type="text" name="name" id="name">&nbsp;&nbsp;<input id="button" type="button" value="查询">
                </form>
            </div>
            <div id="contr">
                <div id="left">
                    <span id="message"><?php echo $msg; ?></span>
                    <div>
                        <?php if (isset($rows) && is_array($rows)) { ?>
                            <?php foreach ($rows as $key => $val) { ?>
                                <span><?php echo $val['name'] ?></span> <span>距离为(<?php echo $val['age']; ?>米)</span></br>
                            <?php }
                        } ?>
                    </div>
                </div>  
                <div id="container"></div>
            </div>
        </div>
        <input type="hidden" name="lnts" id="lnts" value="<?php echo $lat; ?>">
        <input type="hidden" name="longs" id="longs" value="<?php echo $long; ?>">
        <script>
            document.getElementById("button").onclick = function () {
                var n_nam = document.getElementById("name").value;
                if (n_nam == "" || n_nam == null) {
                    alert("请输入搜索地址");
                    return false;
                } else {
                    document.getElementById("form").submit();
                }
            }
            var map = new AMap.Map('container', {
                //resizeEnable: true,
                zoom: 13,
                //center: [116.40, 39.91]
            });
            AMap.plugin('AMap.Geocoder', function () {
                var geocoder = new AMap.Geocoder({
                    city: "010"//城市，默认：“全国”
                });
                var marker = new AMap.Marker({
                    map: map,
                    bubble: true,
                    visible: false
                })
                var lnt = document.getElementById('lnt');
                var long = document.getElementById('long');
                var name = document.getElementById('name');
                var message = document.getElementById('message');
                map.on('click', function (e) {
                    map.remove(markers);
                    marker.setPosition(e.lnglat);
                    marker.show();
                    geocoder.getAddress(e.lnglat, function (status, result) {
                        if (status == 'complete') {

                            lnt.value = e.lnglat.lat;
                            long.value = e.lnglat.lng;
                            name.value = result.regeocode.formattedAddress;
                        } else {
                        }
                    })
                })

                name.onchange = function (e) {
                    var address = name.value;
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
                            //message.innerHTML = ''
                        } else {
                            var message = document.getElementById('message');
                            message.innerHTML = '无法获取位置'
                        }
                    })
                }

            });

            var result =<?php echo $act; ?>;
            //var markers = [], positions = [[116.41, 39.91], [116.42, 39.91], [116.42, 39.92], [116.43, 39.91], [116.39, 39.91]];
            var markers = [], positions = result;
            for (var i = 0, marker; i < positions.length; i++) {
                marker = new AMap.Marker({
                    map: map,
                    icon: 'http://webapi.amap.com/theme/v1.3/markers/n/mark_b1.png',
                    position: positions[i]
                });
                markers.push(marker);
            }
            var show_lnt = document.getElementById('lnts').value;
            var show_long = document.getElementById('longs').value;
            if (show_lnt != "" && show_lnt != null && show_lnt != undefined) {
                marker = new AMap.Marker({
                    map: map,
                    icon: 'http://webapi.amap.com/theme/v1.3/markers/n/mark_bs.png',
                    position: [show_long, show_lnt]
                });
                markers.push(marker);
            }
        </script>
    </body>
</html>