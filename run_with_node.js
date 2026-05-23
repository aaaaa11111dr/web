#!/usr/bin/env node

const { spawn } = require('child_process');

console.log('='.repeat(65));
console.log('🚀 启动公网访问隧道');
console.log('='.repeat(65));
console.log();

// 清理旧进程
console.log('清理旧进程...');
try {
    spawn('pkill', ['-f', 'lt --port']);
    spawn('pkill', ['-f', 'cloudflared']);
} catch(e) {}

console.log();
console.log('启动 LocalTunnel...');
console.log('等待地址...');
console.log('-'.repeat(65));

const lt = spawn('lt', ['--port', '8000']);
let url = null;

lt.stdout.on('data', (data) => {
    const str = data.toString();
    process.stdout.write(str);
    
    const match = str.match(/https:\/\/[^\s]+\.loca\.lt/);
    if (match && !url) {
        url = match[0];
        console.log();
        console.log();
        console.log('='.repeat(65));
        console.log('✅ 公网访问地址获取成功！');
        console.log('='.repeat(65));
        console.log('🌐 主站：' + url);
        console.log('⚙️  管理：' + url + '/goolehome.php');
        console.log('='.repeat(65));
        console.log();
        console.log('📝 登录：admin / Admin1234');
        console.log('='.repeat(65));
        console.log();
        console.log('💡 复制上面的地址在浏览器打开！');
        console.log('='.repeat(65));
        
        const fs = require('fs');
        fs.writeFileSync('/workspace/got_url.txt', url);
    }
});

lt.stderr.on('data', (data) => {
    process.stderr.write(data);
});

setTimeout(() => {
    if (!url) {
        console.log();
        console.log();
        console.log('尝试 Cloudflare Tunnel...');
        lt.kill();
        
        const cf = spawn('npx', ['--yes', 'cloudflared', 'tunnel', '--url', 'http://localhost:8000']);
        cf.stdout.on('data', (data) => {
            const str = data.toString();
            process.stdout.write(str);
            const match = str.match(/https:\/\/[^\s]+\.trycloudflare\.com/);
            if (match) {
                const url = match[0];
                console.log();
                console.log();
                console.log('='.repeat(65));
                console.log('✅ CF 地址：' + url);
                console.log('管理：' + url + '/goolehome.php');
                console.log('登录：admin / Admin1234');
                console.log('='.repeat(65));
                require('fs').writeFileSync('/workspace/got_url.txt', url);
            }
        });
        cf.stderr.on('data', (data) => process.stderr.write(data));
    }
}, 15000);
