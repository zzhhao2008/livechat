<?php
session_start();
if (!$_SESSION['tempname']) {
    $_SESSION['tempname'] = '游客' . round(time() / 100) % 1000 . rand(1000, 9999);
}
if ($_GET['ch']):
    $_SESSION['ch'] = htmlspecialchars($_GET['ch']);
endif;
$hlsport = file_get_contents('https://zsvstudio.top/api/STUN-PORT/index.php?service=hls');
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="utf-8">
    <link rel="shortcut icon" href="./index_files/logo.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="在线直播与聊天" name="description">
    <meta content="在线直播与聊天，ZSV Studio ,淄博实验中学, 网络工作室组织, ZZH Server" name="keywords">
    <link rel="stylesheet" href="./index_files/bootstrap.min.css">
    <link rel="stylesheet" href="./index_files/public.css">
    <link rel="stylesheet" href="./index_files/tool.css">
    <link rel="stylesheet" href="./index_files/c19.css">
    <script src="./index_files/bootstrap.bundle.js.下载"></script>
    <script src="./index_files/view.js.下载"></script>
    <script src="./index_files/ace.js.下载"></script>
    <script src="./index_files/aceinit.js.下载"></script>
    <script src="./index_files/chart.js.下载"></script>
    <script src="./index_files/chart-require copy.js.下载"></script>
    <style>
        #hlsnaver {
            border-bottom: 2px solid red;
        }

        body {
            color: #000;
            padding-top: 80px;
            background: none;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 100vw;
            z-index: -1;
            background: rgb(246, 246, 246);
            background-size: cover;
        }

        .nav-item a:visited,
        .nav-item a:link,
        .navbar-brand {
            color: #333;
            font-weight: 600;
        }

        .nav-item a:hover,
        .navbar-brand:hover {
            color: #000;
            font-weight: 800;
            text-shadow: 1px 2px 2px gainsboro;
            border-bottom: 1px solid pink;
        }

        .navtopc {
            background: #ddd;
        }

        .navmainc {
            background: rgb(246, 246, 246);
            color: #000;
        }

        .abox,
        .problembox>div,
        .problemsubbox>div,
        pre,
        .dropdown-menu,
        .dropdown-menu:hover,
        .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.9);
        }

        .active-item {
            border-bottom: 2px solid #FFFF00;
        }
    </style>

    <title>在线直播与聊天-ZSV Studio</title>
</head>

<body>
    <nav class="navbar fixed-top navtopc">
        <div class="container">
            <a class="navbar-brand" href="https://zsvstudio.top/" style="font-weight: 400;line-height: 1.5;">
                <img src="./index_files/logo.png" alt="Logo" width="30" height="30"
                    class="d-inline-block align-text-top rounded-circle">
                ZSV</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
                aria-controls="offcanvasNavbar" aria-label="切换导航菜单">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="offcanvas offcanvas-start navmainc" tabindex="-1" id="offcanvasNavbar"
                aria-labelledby="offcanvasNavbarLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasNavbarLabel">
                        <img src="./index_files/logo.png" alt="Logo" width="30" height="30"
                            class="d-inline-block align-text-top rounded-circle">
                        直播与互动-ZSV Studio
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                        <li class="nav-item">
                            <a class="nav-link" id="kcsnaver" href="https://zsvstudio.top/kcs"><i
                                    class="bi bi-buildings-fill"></i>电创社</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="productsnaver" href="https://zsvstudio.top/products"><i
                                    class="bi bi-building-fill-check"></i>产品</a>
                        </li>
                        <li class="border-top my-3"></li>
                    </ul>
                    <hr>

                </div>
            </div>
        </div>
    </nav>
    <div class="container main">
        <h5 class="">你好！<?= $_SESSION['tempname'] ?></h5>
        <div class="row">
            <div class="col-md-8">
                <form>
                    <div class="input-group">
                        <input type="text" class="form-control" name="ch" placeholder="请输入频道号" value="<?= $_SESSION['ch'] ?? ''; ?>">
                        <span class="input-group-btn">
                            <button class="btn btn-primary" type="submit">播放</button>
                        </span>
                    </div>
                </form>
                <div class="mb-3 p-2">
                    <label>清晰度</label>
                    <select id="df" class="form-select">
                        <option value="hd720">720P</option>
                        <option value="high">高</option>
                        <option value="mid">中</option>
                        <option value="low">低</option>
                        <option value="src">原画</option>
                    </select>
                </div>
                <p></p>

                <?php
                if ($_SESSION['ch']):
                ?>
                    <script src="/HLS streaming_files/jquery-3.4.1.min.js.下载" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
                    <script src="/HLS streaming_files/bootstrap.min.js.下载" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
                    <script src="/HLS streaming_files/hls.js@latest"></script>
                    <div class="well" id="playerB">
                        <div class="embed-responsive embed-responsive-16by9">
                            <video id="video" style="height:calc(100vh-150px);width:100%" class="embed-responsive-item video-js vjs-default-skin" controls="" muted="muted" src="blob:http://cn-nb-1.ioll.cc:30031/9195a466-2949-4021-85f2-a028bba22932"></video>
                        </div>
                    </div>

                    <script>
                        if (Hls.isSupported()) {
                            var video = document.getElementById('video');
                            hls = new Hls();
                            hls.loadSource('http://hlslive-s.zsvstudio.top:<?= $hlsport ?>/hls/<?= $_SESSION['ch'] ?>_hd720.m3u8');
                            hls.attachMedia(video);
                            hls.on(Hls.Events.MANIFEST_PARSED, function() {
                                video.play();
                            });
                            document.getElementById("df").addEventListener("change", function() {
                                let quality = this.value;
                                let video = document.getElementById('video');
                                let baseUrl = 'http://hlslive-s.zsvstudio.top:<?= $hlsport ?>/hls/<?= $_SESSION['ch'] ?>_' + quality + '.m3u8';
                                hls.loadSource(baseUrl);
                                hls.attachMedia(video);
                                hls.on(Hls.Events.MANIFEST_PARSED, function() {
                                    video.play();
                                });
                            });
                            hls.onerror = function(event, data) {
                                console.error('HLS error:', event, data);
                                document.getElementById('playerB').innerHTML = '<p class="text-danger">HLS播放出错，请检查频道号是否正确。</p>';
                            };
                            //如果源404错误
                            hls.on(Hls.Events.ERROR, function(event, data) {
                                if (data.details === Hls.ErrorDetails.MANIFEST_LOAD_ERROR) {
                                    document.getElementById('playerB').innerHTML = '<p class="text-danger">HLS播放出错，请检查频道号是否正确。或是否已经开播</p>';
                                }
                            });
                        } else {
                            console.error('HLS is not supported in this browser.');
                            document.getElementById('playerB').innerHTML = '<p class="text-danger">您的浏览器不支持HLS播放，请使用支持HLS的浏览器。</p>';
                        }
                    </script>

                    <p></p>
            </div>
            <div class="col-md-4">
                <span id="onlines" class="ms-2"></span>人在线
                <div id="msgs" class="p-2">
                    <!--li><code>9527</code>:88</li-->
                </div>
                <div class="input-group mt-1">
                    <input type="text" class="form-control" placeholder="发送消息" id="i">
                    <button class="btn btn-success" type="submit" id="sendButton">Go</button>
                </div>
                <script>
                    // 全局变量
                    let websocket;
                    let isConnected = false;

                    // 初始化WebSocket连接
                    function initWebSocket() {
                        const serverUrl = "ws://live.zsvstudio.top:3333";

                        try {
                            websocket = new WebSocket(serverUrl);

                            websocket.onopen = function() {
                                isConnected = true;
                                console.log("WebSocket连接已建立");

                                // 发送注册信息
                                const registerMsg = {
                                    tempname: "<?= $_SESSION['tempname'] ?>",
                                    ch: "<?= $_SESSION['ch'] ?>",
                                    type: "join"
                                };
                                websocket.send(JSON.stringify(registerMsg));
                            };

                            websocket.onmessage = function(event) {
                                try {
                                    const data = JSON.parse(event.data);
                                    handleServerMessage(data);
                                } catch (e) {
                                    console.error("消息解析错误:", e);
                                }
                            };

                            websocket.onerror = function(error) {
                                console.error("WebSocket错误:", error);
                                addSystemMessage("连接到服务器时出错");
                            };

                            websocket.onclose = function() {
                                isConnected = false;
                                console.log("WebSocket连接已关闭");
                                addSystemMessage("连接已断开，请刷新页面重新连接");
                            };

                        } catch (error) {
                            console.error("WebSocket初始化错误:", error);
                        }
                    }

                    // 处理服务器消息
                    function handleServerMessage(data) {
                        if (data.type === "join" || data.type === "leave") {
                            // 处理用户加入/离开消息
                            addSystemMessage(`${data.tempname} ${data.type === "join" ? "加入" : "离开"}了聊天室`);
                            updateOnlineCount(data.nowoc);
                        } else if (data.type === "msg") {
                            // 处理普通消息
                            addMessage(data.tempname, data.msg);
                            updateOnlineCount(data.nowoc);
                        } else if (data.res === "ok" && data.online !== undefined) {
                            // 处理注册成功响应
                            updateOnlineCount(data.online);
                            addSystemMessage("您已成功加入聊天室");
                        }
                    }

                    // 添加系统消息
                    function addSystemMessage(message) {
                        const msgsContainer = document.getElementById('msgs');
                        const li = document.createElement('li');
                        li.innerHTML = `<span class="system-msg">${message}</span>`;
                        msgsContainer.appendChild(li);
                        scrollToBottom();
                    }

                    // 添加普通消息
                    function addMessage(username, message) {
                        const msgsContainer = document.getElementById('msgs');
                        const li = document.createElement('li');
                        li.innerHTML = `<code>${username}</code>: ${message}`;
                        msgsContainer.appendChild(li);
                        scrollToBottom();
                    }

                    // 更新在线人数
                    function updateOnlineCount(count) {
                        document.getElementById('onlines').textContent = count;
                    }

                    // 滚动到底部
                    function scrollToBottom() {
                        const msgsContainer = document.getElementById('msgs');
                        msgsContainer.scrollTop = msgsContainer.scrollHeight;
                    }

                    // 发送消息
                    function sendMessage() {

                        if (!isConnected) {
                            addSystemMessage("未连接到服务器，无法发送消息");
                            return;
                        }

                        const input = document.getElementById('i');
                        const message = input.value.trim();
                        if (message) {
                            const msgData = {
                                tempname: "<?= $_SESSION['tempname'] ?>",
                                ch: "<?= $_SESSION['ch'] ?>",
                                msg: message,
                                type: "msg"
                            };

                            websocket.send(JSON.stringify(msgData));
                            input.value = '';
                        }
                    }

                    // 页面加载完成后初始化
                    document.addEventListener('DOMContentLoaded', function() {
                        // 初始化WebSocket连接
                        initWebSocket();

                        // 绑定发送按钮事件
                        const sendButton = document.getElementById('sendButton');
                        sendButton.addEventListener('click', sendMessage);

                        // 绑定输入框回车事件
                        const input = document.getElementById('i');
                        input.addEventListener('keypress', function(e) {
                            if (e.key === 'Enter') {
                                sendMessage();
                            }
                        });

                    });
                </script>
            <?php
                endif;
            ?>
            </div>
        </div>
    </div>
    <style>
        #msgs {
            height: calc(100% - 82px);
            max-height: calc(70vh - 82px);
            overflow-y: auto;
            text-wrap: break-word;
            word-wrap: break-word;
            background: #fff;
        }

        #msgs code {
            color: blueviolet;
            font-weight: 600;
            font-size: large;
        }

        li {
            margin: 0
        }

        .system-msg {
            color: #6c757d;
            font-style: italic;
        }
    </style>

</body>

</html>