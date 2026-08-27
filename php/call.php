<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db_controller.php';
$conn->select_db("Alumni");

if (!isset($_SESSION['logged_account'])) {
    header('Location: login.php');
    exit();
}

$roomId = $_GET['room'] ?? 'room_' . uniqid();
$userEmail = $_SESSION['logged_account']['email'];
$userName = $_SESSION['logged_account']['email'];

// Get user name
$userQuery = $conn->prepare("SELECT first_name, last_name FROM user_table WHERE email = ?");
$userQuery->bind_param("s", $userEmail);
$userQuery->execute();
$userResult = $userQuery->get_result();
if ($userResult->num_rows > 0) {
    $user = $userResult->fetch_assoc();
    $userName = $user['first_name'] . ' ' . $user['last_name'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Video Call</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            background: #1a1a1a;
            overflow: hidden;
        }
        #container {
            position: relative;
            width: 100vw;
            height: 100vh;
            background: #000;
        }
        video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            background: #000;
        }
        #remoteVideo {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        #localVideo {
            position: absolute;
            bottom: 20px;
            right: 20px;
            width: 200px;
            height: 150px;
            border: 3px solid #fff;
            border-radius: 10px;
            z-index: 10;
        }
        #controls {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 20px;
            z-index: 20;
        }
        button {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: none;
            font-size: 24px;
            cursor: pointer;
            transition: all 0.3s;
            color: white;
        }
        button:hover { transform: scale(1.1); }
        #muteBtn { background: #4CAF50; }
        #muteBtn.muted { background: #f44336; }
        #videoBtn { background: #2196F3; }
        #videoBtn.off { background: #f44336; }
        #hangupBtn { background: #f44336; }
        #status {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 15px 25px;
            border-radius: 25px;
            font-size: 16px;
            z-index: 15;
        }
        #waiting {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: white;
            z-index: 5;
        }
        .spinner {
            border: 4px solid rgba(255,255,255,0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div id="container">
        <video id="remoteVideo" autoplay playsinline></video>
        <video id="localVideo" autoplay muted playsinline></video>
        
        <div id="status">Initializing...</div>
        
        <div id="waiting">
            <div class="spinner"></div>
            <h2>Connecting...</h2>
            <p id="statusMsg">Please wait</p>
        </div>
        
        <div id="controls" style="display:none;">
            <button id="muteBtn" title="Mute">🎤</button>
            <button id="videoBtn" title="Video">📹</button>
            <button id="hangupBtn" title="Hang Up">📞</button>
        </div>
    </div>

    <script>
        const roomId = '<?php echo $roomId; ?>';
        const userEmail = '<?php echo $userEmail; ?>';
        
        let localStream, peerConnection;
        let isMuted = false, isVideoOff = false;
        
        const servers = {
            iceServers: [
                { urls: 'stun:stun.l.google.com:19302' },
                { urls: 'stun:stun1.l.google.com:19302' },
                { urls: 'stun:stun2.l.google.com:19302' }
            ]
        };
        
        // Update status
        function updateStatus(msg) {
            document.getElementById('status').textContent = msg;
            document.getElementById('statusMsg').textContent = msg;
            console.log('📱', msg);
        }
        
        // Initialize
        async function init() {
            try {
                updateStatus('Requesting camera and microphone...');
                
                // Get media
                localStream = await navigator.mediaDevices.getUserMedia({
                    video: { width: 1280, height: 720 },
                    audio: {
                        echoCancellation: true,
                        noiseSuppression: true,
                        autoGainControl: true
                    }
                });
                
                document.getElementById('localVideo').srcObject = localStream;
                updateStatus('Media ready');
                
                // Create peer connection
                peerConnection = new RTCPeerConnection(servers);
                
                // Add tracks
                localStream.getTracks().forEach(track => {
                    peerConnection.addTrack(track, localStream);
                    console.log('✅ Added track:', track.kind);
                });
                
                // Handle incoming tracks
                peerConnection.ontrack = event => {
                    console.log('📥 Received track:', event.track.kind);
                    const remoteVideo = document.getElementById('remoteVideo');
                    if (!remoteVideo.srcObject) {
                        remoteVideo.srcObject = event.streams[0];
                        remoteVideo.muted = false;
                        remoteVideo.volume = 1.0;
                        
                        remoteVideo.play().then(() => {
                            console.log('▶️ Remote video playing');
                            document.getElementById('waiting').style.display = 'none';
                            document.getElementById('controls').style.display = 'flex';
                            updateStatus('Connected ✓');
                        }).catch(e => {
                            console.error('Play error:', e);
                            updateStatus('Click to enable audio');
                            document.body.onclick = () => remoteVideo.play();
                        });
                    }
                };
                
                // Handle ICE candidates
                peerConnection.onicecandidate = event => {
                    if (event.candidate) {
                        sendSignal('candidate', event.candidate);
                    }
                };
                
                // Monitor connection
                peerConnection.onconnectionstatechange = () => {
                    const state = peerConnection.connectionState;
                    console.log('🔗 Connection:', state);
                    updateStatus('Connection: ' + state);
                    
                    if (state === 'connected') {
                        document.getElementById('waiting').style.display = 'none';
                        document.getElementById('controls').style.display = 'flex';
                        updateStatus('Connected ✓');
                    } else if (state === 'failed' || state === 'disconnected') {
                        updateStatus('Connection lost');
                    }
                };
                
                // Start signaling
                updateStatus('Setting up connection...');
                startSignaling();
                
            } catch (error) {
                console.error('❌ Error:', error);
                updateStatus('Error: ' + error.message);
                alert('Failed to access camera/microphone.\n\n' + error.message + '\n\nPlease check browser permissions.');
            }
        }
        
        // Signaling
        let isInitiator = false;
        let lastMsgId = 0;
        
        async function startSignaling() {
            // Check if we're first in room
            const response = await fetch(`signaling_server.php?room_id=${roomId}&last_id=0`);
            const data = await response.json();
            
            isInitiator = !data.messages || data.messages.length === 0;
            
            if (isInitiator) {
                console.log('👑 I am initiator');
                updateStatus('Creating offer...');
                const offer = await peerConnection.createOffer();
                await peerConnection.setLocalDescription(offer);
                await sendSignal('offer', offer);
                updateStatus('Waiting for peer...');
            } else {
                console.log('👤 I am joiner');
                updateStatus('Waiting for offer...');
            }
            
            // Start polling
            pollSignals();
        }
        
        async function sendSignal(type, data) {
            try {
                await fetch('signaling_server.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        room_id: roomId,
                        type: type,
                        data: data
                    })
                });
                console.log('📤 Sent:', type);
            } catch (error) {
                console.error('Send error:', error);
            }
        }
        
        async function pollSignals() {
            try {
                const response = await fetch(`signaling_server.php?room_id=${roomId}&last_id=${lastMsgId}`);
                const data = await response.json();
                
                if (data.messages) {
                    for (const msg of data.messages) {
                        await handleSignal(msg);
                        lastMsgId = msg.id;
                    }
                }
            } catch (error) {
                console.error('Poll error:', error);
            }
            
            setTimeout(pollSignals, 500);
        }
        
        async function handleSignal(msg) {
            try {
                console.log('📥 Received:', msg.type);
                
                switch (msg.type) {
                    case 'offer':
                        updateStatus('Received offer, creating answer...');
                        await peerConnection.setRemoteDescription(new RTCSessionDescription(msg.data));
                        const answer = await peerConnection.createAnswer();
                        await peerConnection.setLocalDescription(answer);
                        await sendSignal('answer', answer);
                        updateStatus('Answer sent, connecting...');
                        break;
                        
                    case 'answer':
                        updateStatus('Received answer, connecting...');
                        await peerConnection.setRemoteDescription(new RTCSessionDescription(msg.data));
                        break;
                        
                    case 'candidate':
                        await peerConnection.addIceCandidate(new RTCIceCandidate(msg.data));
                        break;
                }
            } catch (error) {
                console.error('Handle error:', error);
            }
        }
        
        // Controls
        document.getElementById('muteBtn').onclick = () => {
            isMuted = !isMuted;
            localStream.getAudioTracks().forEach(t => t.enabled = !isMuted);
            document.getElementById('muteBtn').classList.toggle('muted', isMuted);
            document.getElementById('muteBtn').textContent = isMuted ? '🔇' : '🎤';
        };
        
        document.getElementById('videoBtn').onclick = () => {
            isVideoOff = !isVideoOff;
            localStream.getVideoTracks().forEach(t => t.enabled = !isVideoOff);
            document.getElementById('videoBtn').classList.toggle('off', isVideoOff);
            document.getElementById('videoBtn').textContent = isVideoOff ? '📷' : '📹';
        };
        
        document.getElementById('hangupBtn').onclick = () => {
            if (confirm('End call?')) {
                if (localStream) localStream.getTracks().forEach(t => t.stop());
                if (peerConnection) peerConnection.close();
                window.location.href = '<?php echo $_SESSION['logged_account']['type'] == 'admin' ? 'main_menu_admin.php' : 'main_menu.php'; ?>';
            }
        };
        
        // Start
        window.onload = init;
        
        // Cleanup
        window.onbeforeunload = () => {
            if (localStream) localStream.getTracks().forEach(t => t.stop());
            if (peerConnection) peerConnection.close();
        };
    </script>
</body>
</html>
