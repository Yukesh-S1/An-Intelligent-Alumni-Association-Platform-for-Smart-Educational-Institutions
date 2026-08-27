<!DOCTYPE html>
<html>
<head>
    <title>Video Call</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #000; font-family: Arial; }
        #videos { position: relative; width: 100vw; height: 100vh; }
        video { width: 100%; height: 100%; object-fit: cover; background: #000; }
        #remote { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
        #local { 
            position: absolute; 
            bottom: 20px; 
            right: 20px; 
            width: 200px; 
            height: 150px; 
            border: 3px solid #fff; 
            border-radius: 10px;
        }
        #controls { 
            position: absolute; 
            bottom: 30px; 
            left: 50%; 
            transform: translateX(-50%);
            display: flex;
            gap: 20px;
        }
        button {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: white;
        }
        #mute { background: #4CAF50; }
        #mute.off { background: #f44336; }
        #video { background: #2196F3; }
        #video.off { background: #f44336; }
        #hang { background: #f44336; }
        #status {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 15px 25px;
            border-radius: 25px;
        }
    </style>
</head>
<body>
    <div id="videos">
        <video id="remote" autoplay playsinline></video>
        <video id="local" autoplay muted playsinline></video>
        <div id="status">Initializing...</div>
        <div id="controls" style="display:none;">
            <button id="mute">🎤</button>
            <button id="video">📹</button>
            <button id="hang">📞</button>
        </div>
    </div>

    <script>
        const roomId = new URLSearchParams(window.location.search).get('room') || 'room_' + Date.now();
        let local, pc;
        let muted = false, videoOff = false;
        
        const config = {
            iceServers: [
                { urls: 'stun:stun.l.google.com:19302' },
                { urls: 'stun:stun1.l.google.com:19302' }
            ]
        };
        
        function log(msg) {
            document.getElementById('status').textContent = msg;
            console.log(msg);
        }
        
        async function start() {
            try {
                log('Getting media...');
                local = await navigator.mediaDevices.getUserMedia({
                    video: true,
                    audio: { echoCancellation: true, noiseSuppression: true }
                });
                
                document.getElementById('local').srcObject = local;
                log('Media ready');
                
                pc = new RTCPeerConnection(config);
                local.getTracks().forEach(t => pc.addTrack(t, local));
                
                pc.ontrack = e => {
                    const remote = document.getElementById('remote');
                    remote.srcObject = e.streams[0];
                    remote.muted = false;
                    remote.volume = 1.0;
                    remote.play().then(() => {
                        log('Connected ✓');
                        document.getElementById('controls').style.display = 'flex';
                    });
                };
                
                pc.onicecandidate = e => {
                    if (e.candidate) send('candidate', e.candidate);
                };
                
                pc.onconnectionstatechange = () => {
                    log('Connection: ' + pc.connectionState);
                };
                
                // Check if we're first
                const resp = await fetch(`signaling_server.php?room_id=${roomId}&last_id=0`);
                const data = await resp.json();
                const isFirst = !data.messages || data.messages.length === 0;
                
                if (isFirst) {
                    log('Creating offer...');
                    const offer = await pc.createOffer();
                    await pc.setLocalDescription(offer);
                    await send('offer', offer);
                    log('Waiting for peer...');
                } else {
                    log('Waiting for offer...');
                }
                
                poll();
                
            } catch (error) {
                log('Error: ' + error.message);
                alert('Failed to access camera/microphone.\n\n' + error.message);
            }
        }
        
        async function send(type, data) {
            await fetch('signaling_server.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ room_id: roomId, type, data })
            });
        }
        
        let lastId = 0;
        async function poll() {
            try {
                const resp = await fetch(`signaling_server.php?room_id=${roomId}&last_id=${lastId}`);
                const data = await resp.json();
                
                if (data.messages) {
                    for (const msg of data.messages) {
                        await handle(msg);
                        lastId = msg.id;
                    }
                }
            } catch (e) {
                console.error('Poll error:', e);
            }
            setTimeout(poll, 500);
        }
        
        async function handle(msg) {
            try {
                switch (msg.type) {
                    case 'offer':
                        log('Got offer, creating answer...');
                        await pc.setRemoteDescription(msg.data);
                        const answer = await pc.createAnswer();
                        await pc.setLocalDescription(answer);
                        await send('answer', answer);
                        log('Answer sent');
                        break;
                    case 'answer':
                        log('Got answer, connecting...');
                        await pc.setRemoteDescription(msg.data);
                        break;
                    case 'candidate':
                        await pc.addIceCandidate(msg.data);
                        break;
                }
            } catch (e) {
                console.error('Handle error:', e);
            }
        }
        
        document.getElementById('mute').onclick = () => {
            muted = !muted;
            local.getAudioTracks().forEach(t => t.enabled = !muted);
            document.getElementById('mute').classList.toggle('off', muted);
            document.getElementById('mute').textContent = muted ? '🔇' : '🎤';
        };
        
        document.getElementById('video').onclick = () => {
            videoOff = !videoOff;
            local.getVideoTracks().forEach(t => t.enabled = !videoOff);
            document.getElementById('video').classList.toggle('off', videoOff);
            document.getElementById('video').textContent = videoOff ? '📷' : '📹';
        };
        
        document.getElementById('hang').onclick = () => {
            if (confirm('End call?')) {
                if (local) local.getTracks().forEach(t => t.stop());
                if (pc) pc.close();
                window.close();
            }
        };
        
        window.onload = start;
        window.onbeforeunload = () => {
            if (local) local.getTracks().forEach(t => t.stop());
            if (pc) pc.close();
        };
    </script>
</body>
</html>
