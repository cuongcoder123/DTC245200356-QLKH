import asyncio
import json
import urllib.request
import base64
import os
import sys
import subprocess
import time
from tornado.websocket import websocket_connect

if sys.platform == 'win32':
    sys.stdout.reconfigure(encoding='utf-8')

async def capture_test_chapter():
    html_path = os.path.abspath('BAO_CAO_BAI_TAP_LON_HOAN_CHINH.html').replace('\\', '/')
    file_url = f"file:///{html_path}"
    out_file = os.path.abspath('docs/images/test_chapter_preview.png')
    
    user_data = os.path.expandvars('%TEMP%\\chrome_screenshot_pages')
    chrome_proc = subprocess.Popen([
        r'C:\Program Files\Google\Chrome\Application\chrome.exe',
        '--headless=new',
        '--remote-debugging-port=9223',
        '--disable-gpu',
        '--window-size=2133,1200',
        '--no-first-run',
        f'--user-data-dir={user_data}'
    ])
    
    try:
        time.sleep(2.0)
        req = urllib.request.Request('http://localhost:9223/json/new?about:blank', method='PUT')
        resp = urllib.request.urlopen(req)
        tab_info = json.loads(resp.read().decode())
        ws_url = tab_info['webSocketDebuggerUrl']
        ws = await websocket_connect(ws_url)
        
        cmd_id = 1
        async def send(method, params=None):
            nonlocal cmd_id
            cid = cmd_id
            cmd_id += 1
            payload = {'id': cid, 'method': method}
            if params:
                payload['params'] = params
            await ws.write_message(json.dumps(payload))
            while True:
                msg = await ws.read_message()
                data = json.loads(msg)
                if data.get('id') == cid:
                    return data.get('result', {})

        await send('Page.enable')
        await send('Runtime.enable')
        await send('Page.navigate', {'url': file_url})
        await asyncio.sleep(2.0)
        
        # Scroll to page 13
        await send('Runtime.evaluate', {
            'expression': 'document.getElementById("page-13").scrollIntoView();'
        })
        await asyncio.sleep(1.0)
        
        shot_res = await send('Page.captureScreenshot', {'format': 'png'})
        img_data = base64.b64decode(shot_res['data'])
        with open(out_file, 'wb') as f:
            f.write(img_data)
        print("Đã chụp thành công:", out_file)
    finally:
        chrome_proc.terminate()

if __name__ == '__main__':
    asyncio.run(capture_test_chapter())
