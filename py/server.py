
"""
功能：在81端口启动一个简单的WS服务器，负责处理直播的互动消息
当接收连接，收到数据
{
    "tempname":"",
    "ch":""
}
表示注册该用户为<ch>频道名为<tempname>的用户
返回
{
    "res":"ok",
    "online":0 //当前频道在线人数
}
并向该频道的所有用户发送
{
    "tempname":"",
    "ch":"<ch>",
    "msg":"加入",
    "type":"join",
    "nowoc":0 //当前频道在线人数
}
用户发送信息的请求
{
    "tempname":"",
    "ch":"",
    "msg":"",
    "type":"msg"
}
群发信息的格式
{
    "tempname":"",//发送者名称
    "ch":"<ch>",
    "msg":"<msg>",
    "type":"msg",
    "nowoc":0 //当前频道在线人数
}
用户离开时回发送
{
    "tempname":"",//离开者名称
    "ch":"<ch>",
    "msg":"离开",
    "type":"leave",
    "nowoc":0 //当前频道在线人数
}
若频道内没有人则删除频道
* 所有用户数据都需要经过HTML混淆，防止CSS注入
"""
import asyncio
import websockets
import json
import html

# 存储频道信息
channels = {}


def sanitize_text(text):
    """HTML混淆，防止CSS注入"""
    if text is None:
        return ""
    return html.escape(str(text))


def get_channel_online_count(channel_name):
    """获取频道在线人数"""
    if channel_name in channels:
        return len(channels[channel_name])
    return 0


def remove_empty_channels():
    """删除空频道"""
    empty_channels = [ch for ch, users in channels.items() if len(users) == 0]
    for ch in empty_channels:
        del channels[ch]


async def handle_message(websocket, message_data):
    """处理消息"""
    try:
        print(f"Received message: {message_data}")
        # 获取消息类型
        msg_type = message_data.get("type", "")
        tempname = sanitize_text(message_data.get("tempname", ""))
        ch = sanitize_text(message_data.get("ch", ""))

        if msg_type == "join":
            # 用户加入频道
            if ch not in channels:
                channels[ch] = {}
            
            # 存储用户连接
            channels[ch][tempname] = websocket

            # 获取当前在线人数
            online_count = get_channel_online_count(ch)

            # 向频道所有用户发送加入消息
            join_message = {
                "tempname": tempname,
                "ch": ch,
                "msg": "加入",
                "type": "join",
                "nowoc": online_count
            }

            # 广播给频道内所有用户
            if ch in channels:
                disconnected_users = []
                for username, ws in channels[ch].items():
                    try:
                        await ws.send(json.dumps(join_message))
                    except websockets.exceptions.ConnectionClosed:
                        disconnected_users.append(username)

                # 移除断开连接的用户
                for username in disconnected_users:
                    del channels[ch][username]

                # 检查是否需要删除空频道
                if len(channels[ch]) == 0:
                    remove_empty_channels()
            
            # 返回确认消息
            response = {
                "res": "ok",
                "online": online_count
            }
            print(f"{tempname} 加入 {ch}")
            await websocket.send(json.dumps(response))

        elif msg_type == "msg":
            # 用户发送消息
            msg = sanitize_text(message_data.get("msg", ""))

            #判断用户是否在频道内
            if ch not in channels or tempname not in channels[ch]:
                print(f"{tempname} 尝试发送消息到不存在的频道 {ch}")
                return
            if not msg:
                print(f"{tempname} 发送了空消息")
                return
            if len(msg) > 1000:
                print(f"{tempname} 发送了过长的消息")
                return
            if(channels[ch][tempname] != websocket):
                print(f"{tempname} 尝试使用错误的WebSocket连接发送消息")
                try:
                    await websocket.close(code=1008, reason="Invalid WebSocket")
                except Exception as e:
                    print(f"处理连接时出错: {str(e)}")
                    traceback.print_exc()
                return
            
            print(f"{tempname} 在 {ch} 发送消息: {msg}")
            # 获取当前在线人数
            online_count = get_channel_online_count(ch)

            # 构造群发消息
            broadcast_message = {
                "tempname": tempname,
                "ch": ch,
                "msg": msg,
                "type": "msg",
                "nowoc": online_count
            }

            # 广播给频道内所有用户
            if ch in channels:
                disconnected_users = []
                for username, ws in channels[ch].items():
                    try:
                        await ws.send(json.dumps(broadcast_message))
                    except websockets.exceptions.ConnectionClosed:
                        disconnected_users.append(username)

                # 移除断开连接的用户
                for username in disconnected_users:
                    del channels[ch][username]

                # 检查是否需要删除空频道
                if len(channels[ch]) == 0:
                    remove_empty_channels()

    except Exception as e:
        print(f"处理消息时出错: {e}")


async def handle_disconnect(websocket, close_data=None):
    """处理用户断开连接"""
    try:
        # 查找断开连接的用户
        for ch in list(channels.keys()):
            disconnected_users = []
            for tempname, ws in channels[ch].items():
                if ws == websocket:
                    disconnected_users.append(tempname)

            # 移除断开连接的用户并发送离开消息
            for tempname in disconnected_users:
                del channels[ch][tempname]

                # 获取当前在线人数
                online_count = get_channel_online_count(ch)

                # 向频道内剩余用户发送离开消息
                leave_message = {
                    "tempname": tempname,
                    "ch": ch,
                    "msg": "离开",
                    "type": "leave",
                    "nowoc": online_count
                }

                # 广播离开消息
                for username, ws in channels[ch].items():
                    try:
                        await ws.send(json.dumps(leave_message))
                    except websockets.exceptions.ConnectionClosed:
                        pass  # 忽略其他断开连接的用户

                # 检查是否需要删除空频道
                if len(channels[ch]) == 0:
                    remove_empty_channels()
                    break

    except Exception as e:
        print(f"处理断开连接时出错: {e}")


async def websocket_handler(websocket):
    """WebSocket连接处理器"""
    try:
        async for message in websocket:
            try:
                # 解析JSON消息
                message_data = json.loads(message)
                await handle_message(websocket, message_data)
            except json.JSONDecodeError:
                print("无效的JSON格式")
            except Exception as e:
                print(f"处理消息时出错: {e}")

    except websockets.exceptions.ConnectionClosed:
        pass
    except Exception as e:
        print(f"WebSocket连接错误: {e}")
    finally:
        # 处理用户断开连接
        await handle_disconnect(websocket)


async def main():
    """主函数"""
    # 在81端口启动WebSocket服务器
    server = await websockets.serve(websocket_handler, "0.0.0.0", 81)
    print("WebSocket服务器已在81端口启动")

    try:
        await server.wait_closed()
    except KeyboardInterrupt:
        print("服务器正在关闭...")
        server.close()
        await server.wait_closed()

if __name__ == "__main__":
    asyncio.run(main())
