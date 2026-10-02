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

PAGES = [
    {
        'id': '01_dang_nhap',
        'title': 'Giao diện Đăng Nhập Hệ Thống',
        'url': 'https://localhost/login.php',
        'need_auth': False,
        'wait': 2.0
    },
    {
        'id': '02_tong_quan_dashboard',
        'title': 'Bảng Điều Khiển Tổng Quan (Dashboard)',
        'url': 'https://localhost/index.php',
        'need_auth': True,
        'wait': 3.5
    },
    {
        'id': '03_quan_ly_hang_hoa',
        'title': 'Quản Lý Danh Sách Hàng Hóa (Products)',
        'url': 'https://localhost/products.php',
        'need_auth': True,
        'wait': 2.5
    },
    {
        'id': '04_danh_muc_san_pham',
        'title': 'Quản Lý Danh Mục Sản Phẩm (Categories)',
        'url': 'https://localhost/categories.php',
        'need_auth': True,
        'wait': 2.5
    },
    {
        'id': '05_nha_cung_cap',
        'title': 'Quản Lý Nhà Cung Cấp (Suppliers)',
        'url': 'https://localhost/suppliers.php',
        'need_auth': True,
        'wait': 2.5
    },
    {
        'id': '06_giao_dich_nhap_xuat',
        'title': 'Quản Lý Giao Dịch Nhập / Xuất Kho (Transactions)',
        'url': 'https://localhost/transactions.php',
        'need_auth': True,
        'wait': 2.5
    },
    {
        'id': '07_bao_cao_thong_ke',
        'title': 'Báo Cáo & Thống Kê Tài Sản Kho (Reports)',
        'url': 'https://localhost/reports.php',
        'need_auth': True,
        'wait': 3.0
    },
    {
        'id': '08_trung_tam_giam_sat_logql',
        'title': 'Trung Tâm Giám Sát Hạ Tầng & Sandbox LogQL',
        'url': 'https://localhost/system.php',
        'need_auth': True,
        'wait': 3.0
    },
    {
        'id': '09_grafana_dashboard',
        'title': 'Giao Diện Giám Sát Grafana (Dashboard)',
        'url': 'https://localhost/grafana/d/inventory-overview/he-thong-quan-ly-kho-hang-giam-sat-and-logs?kiosk',
        'need_auth': False,
        'wait': 4.0
    },
    {
        'id': '10_phpmyadmin',
        'title': 'Giao Diện Quản Trị CSDL phpMyAdmin',
        'url': 'https://localhost/pma/',
        'need_auth': False,
        'wait': 3.0
    },
    {
        'id': '11_prometheus_targets',
        'title': 'Giao Diện Giám Sát Thu Thập Chỉ Số Prometheus Targets',
        'url': 'https://localhost/prometheus/targets',
        'need_auth': False,
        'wait': 3.5
    }
]

async def capture_all():
    out_dir = os.path.abspath('docs/images')
    os.makedirs(out_dir, exist_ok=True)
    
    user_data = os.path.expandvars('%TEMP%\\chrome_screenshot_bot')
    chrome_proc = subprocess.Popen([
        r'C:\Program Files\Google\Chrome\Application\chrome.exe',
        '--headless=new',
        '--remote-debugging-port=9222',
        '--ignore-certificate-errors',
        '--disable-gpu',
        '--window-size=1600,1000',
        '--no-first-run',
        f'--user-data-dir={user_data}'
    ])
    
    try:
        time.sleep(2.0)
        req = urllib.request.Request('http://localhost:9222/json/new?about:blank', method='PUT')
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

        await send('Network.enable')
        await send('Page.enable')
        await send('Emulation.setDeviceMetricsOverride', {
            'width': 1600,
            'height': 980,
            'deviceScaleFactor': 1,
            'mobile': False
        })

        for p in PAGES:
            print(f"--> Dang chup: {p['title']} ({p['url']})...")
            if p['need_auth']:
                await send('Network.setCookie', {
                    'name': 'PHPSESSID',
                    'value': 'screenshotsession',
                    'domain': 'localhost',
                    'path': '/',
                    'secure': True
                })
            else:
                await send('Network.deleteCookies', {'name': 'PHPSESSID', 'url': 'https://localhost/'})
            
            await send('Page.navigate', {'url': p['url']})
            await asyncio.sleep(p['wait'])
            
            shot_res = await send('Page.captureScreenshot', {'format': 'png'})
            img_data = base64.b64decode(shot_res['data'])
            
            filepath = os.path.join(out_dir, f"{p['id']}.png")
            with open(filepath, 'wb') as f:
                f.write(img_data)
            print(f"    OK: {filepath} ({len(img_data):,} bytes)")

        ws.close()
        print("\nHoan tat chup toan bo 11 anh chuc nang he thong!")
    finally:
        chrome_proc.kill()

if __name__ == '__main__':
    asyncio.run(capture_all())
