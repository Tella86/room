document.addEventListener("DOMContentLoaded", () => {
    const messageForm = document.getElementById("message-form");
    const chatBox = document.getElementById("chat-box");
    const roomSelect = document.getElementById("room-select");
    const recordButton = document.getElementById("record-button");
    const userId = document.body.getAttribute('data-user-id');
    const sound = new Howl({
        src: ['../hms/admin/assets/ezems.wav'], // Ensure this path is correct
        autoplay: false,
        loop: false,
        volume: 1.0
    });

    let mediaRecorder;
    let audioChunks = [];
    let ws;

    // Function to initialize WebSocket connection
    function connectWebSocket() {
        ws = new WebSocket('wss://healthtech.ezems.co.ke:443');

        ws.onopen = function() {
            console.log('WebSocket connection established');
            ws.send(JSON.stringify({ type: 'join', room_id: roomSelect.value }));
        };

        ws.onerror = function(error) {
            console.error('WebSocket Error:', error);
            alert('An error occurred with the WebSocket connection. Please try again later.');
        };

        ws.onclose = function(event) {
            console.warn('WebSocket closed:', event);
            // Attempt to reconnect after a delay
            setTimeout(connectWebSocket, 5000);
        };

        ws.onmessage = function(event) {
            const data = JSON.parse(event.data);
            if (data.type === 'message' && data.room_id === roomSelect.value) {
                const messageElement = createMessageElement(data);
                chatBox.appendChild(messageElement);
                chatBox.scrollTop = chatBox.scrollHeight;

                // Play notification sound when a new message is received
                playNotificationSound();
            } else if (data.type === 'delete' && data.room_id === roomSelect.value) {
                removeMessageElement(data.message_id);
            } else if (data.type === 'audio' && data.room_id === roomSelect.value) {
                const audioElement = createAudioElement(data);
                chatBox.appendChild(audioElement);
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        };
    }

    // Start WebSocket connection
    connectWebSocket();

    // Function to play the notification sound
    function playNotificationSound() {
        sound.play();
    }

    messageForm.addEventListener("submit", function(e) {
        e.preventDefault();

        const messageInput = document.getElementById("message");
        const message = messageInput.value.trim(); // Trim to remove extra spaces
        const roomId = roomSelect.value;

        if (message === "") {
            alert("Message cannot be empty!");
            return;
        }

        const sendButton = messageForm.querySelector("button[type='submit']");
        sendButton.disabled = true; // Disable send button while sending

        fetch('messages.php', { // Ensure the correct path to messages.php
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ action: 'send', message: message, room_id: roomId })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                messageInput.value = '';
                const messageElement = createMessageElement({
                    type: 'message',
                    username: data.username,
                    message: message,
                    room_id: roomId,
                    user_id: userId,
                    message_id: data.message_id,
                    photo: data.photo
                });
                chatBox.appendChild(messageElement);
                chatBox.scrollTop = chatBox.scrollHeight;

                ws.send(JSON.stringify({
                    type: 'message',
                    username: data.username,
                    message: message,
                    room_id: roomId,
                    message_id: data.message_id,
                    photo: data.photo
                }));
            } else {
                console.error('Error:', data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to send message. Please try again.');
        })
        .finally(() => {
            sendButton.disabled = false; // Re-enable send button
        });
    });

    recordButton.addEventListener("click", function() {
        if (mediaRecorder && mediaRecorder.state === "recording") {
            mediaRecorder.stop();
            recordButton.textContent = "Start Recording";
        } else {
            startRecording();
            recordButton.textContent = "Stop Recording";
        }
    });

    function startRecording() {
        navigator.mediaDevices.getUserMedia({ audio: true })
            .then(stream => {
                mediaRecorder = new MediaRecorder(stream);
                mediaRecorder.start();

                mediaRecorder.ondataavailable = event => {
                    audioChunks.push(event.data);
                };

                mediaRecorder.onstop = () => {
                    const audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
                    audioChunks = [];
                    sendAudioMessage(audioBlob);
                };
            })
            .catch(error => {
                console.error('Error accessing microphone:', error);
                alert('Could not access microphone. Please check your permissions.');
            });
    }

    function sendAudioMessage(audioBlob) {
        const roomId = roomSelect.value;
        const formData = new FormData();
        formData.append('audio', audioBlob);
        formData.append('room_id', roomId);

        fetch('messages.php', { // Ensure the correct path to messages.php
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const audioElement = createAudioElement({
                    type: 'audio',
                    username: data.username,
                    audio_url: data.audio_url,
                    room_id: roomId,
                    user_id: userId,
                    message_id: data.message_id,
                    photo: data.photo
                });
                chatBox.appendChild(audioElement);
                chatBox.scrollTop = chatBox.scrollHeight;

                ws.send(JSON.stringify({
                    type: 'audio',
                    username: data.username,
                    audio_url: data.audio_url,
                    room_id: roomId,
                    message_id: data.message_id,
                    photo: data.photo
                }));
            } else {
                console.error('Error:', data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to send audio message. Please try again.');
        });
    }

    roomSelect.addEventListener("change", function() {
        const roomId = roomSelect.value;
        chatBox.innerHTML = ''; // Clear the chat box
        fetch(`messages.php?room_id=${roomId}`) // Ensure the correct path to messages.php
            .then(response => response.json())
            .then(data => {
                data.forEach(message => {
                    const messageElement = createMessageElement(message);
                    chatBox.appendChild(messageElement);
                });
                chatBox.scrollTop = chatBox.scrollHeight;

                ws.send(JSON.stringify({ type: 'join', room_id: roomId }));
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to load messages. Please try again.');
            });
    });

    function createMessageElement(messageData) {
        const div = document.createElement('div');
        div.className = 'message';
        div.setAttribute('data-message-id', messageData.message_id);
        div.innerHTML = `
            <img src="${messageData.photo}" alt="${messageData.username}" class="avatar">
            <div class="message-content">
                <span class="username">${messageData.username}</span>
                <span class="message-text">${messageData.message}</span>
            </div>
        `;
        return div;
    }

    function createAudioElement(audioData) {
        const div = document.createElement('div');
        div.className = 'message';
        div.setAttribute('data-message-id', audioData.message_id);
        div.innerHTML = `
            <img src="${audioData.photo}" alt="${audioData.username}" class="avatar">
            <div class="message-content">
                <span class="username">${audioData.username}</span>
                <audio controls>
                    <source src="${audioData.audio_url}" type="audio/wav">
                    Your browser does not support the audio element.
                </audio>
            </div>
        `;
        return div;
    }

    function removeMessageElement(messageId) {
        const messageElement = document.querySelector(`.message[data-message-id="${messageId}"]`);
        if (messageElement) {
            messageElement.remove();
        }
    }
});
