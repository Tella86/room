document.addEventListener("DOMContentLoaded", () => {
    const messageForm = document.getElementById("message-form");
    const chatBox = document.getElementById("chat-box");
    const roomSelect = document.getElementById("room-select");
    const recordButton = document.getElementById("record-button");
    const ws = new WebSocket('ws://localhost:8080');
    const userId = document.body.getAttribute('data-user-id');
    const recordingIndicator = document.getElementById('recording-indicator');

    let mediaRecorder;
    let audioChunks = [];

    // Initialize Howler.js
    const sound = new Howl({
        src: ['../hms/admin/assets/ezems.wav'],
        autoplay: false,
        loop: false,
        volume: 1.0
    });

    // Function to play the notification sound
    function playNotificationSound() {
        sound.play();
    }

    ws.onopen = function() {
        console.log('WebSocket connection established');
        ws.send(JSON.stringify({ type: 'join', room_id: roomSelect.value }));
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

    messageForm.addEventListener("submit", function(e) {
        e.preventDefault();

        const messageInput = document.getElementById("message");
        const message = messageInput.value;
        const roomId = roomSelect.value;

        fetch('messages.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ action: 'send', message: message, room_id: roomId })
        })
        .then(response => response.json())
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
        });
    });

    recordButton.addEventListener("click", function() {
        if (mediaRecorder && mediaRecorder.state === "recording") {
            mediaRecorder.stop();
            recordButton.textContent = "Start Recording";
            recordingIndicator.classList.add('hidden');
        } else {
            startRecording();
            recordButton.textContent = "Stop Recording";
            recordingIndicator.classList.remove('hidden');
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
            });
    }

    function sendAudioMessage(audioBlob) {
        const roomId = roomSelect.value;
        const formData = new FormData();
        formData.append('audio', audioBlob);
        formData.append('room_id', roomId);

        fetch('messages.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
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
                    photo: data.photo
                }));
            } else {
                console.error('Error:', data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function createMessageElement(data) {
        const messageElement = document.createElement('div');
        messageElement.classList.add('message', data.user_id === userId ? 'sent' : 'received');
        messageElement.dataset.messageId = data.message_id;

        const photoElement = document.createElement('img');
        photoElement.src = data.photo;
        photoElement.alt = 'Photo';
        photoElement.style.width = '30px';
        photoElement.style.height = '30px';
        photoElement.style.borderRadius = '50%';
        messageElement.appendChild(photoElement);

        const textElement = document.createElement('div');
        textElement.classList.add('text');
        textElement.textContent = `${data.username}: ${data.message}`;
        messageElement.appendChild(textElement);

        if (data.user_id === userId) {
            const deleteButton = document.createElement('button');
            deleteButton.classList.add('delete-button');
            deleteButton.textContent = 'X';
            deleteButton.onclick = () => {
                deleteMessage(data.message_id);
            };
            messageElement.appendChild(deleteButton);
        }

        return messageElement;
    }

    function createAudioElement(data) {
        const audioElement = document.createElement('div');
        audioElement.classList.add('message', data.user_id === userId ? 'sent' : 'received');
        audioElement.dataset.messageId = data.message_id;

        const photoElement = document.createElement('img');
        photoElement.src = data.photo;
        photoElement.alt = 'Photo';
        photoElement.style.width = '30px';
        photoElement.style.height = '30px';
        photoElement.style.borderRadius = '50%';
        audioElement.appendChild(photoElement);

        const audioPlayer = document.createElement('audio');
        audioPlayer.controls = true;
        audioPlayer.src = data.audio_url;
        audioElement.appendChild(audioPlayer);

        if (data.user_id === userId) {
            const deleteButton = document.createElement('button');
            deleteButton.classList.add('delete-button');
            deleteButton.textContent = 'X';
            deleteButton.onclick = () => {
                deleteMessage(data.message_id);
            };
            audioElement.appendChild(deleteButton);
        }

        return audioElement;
    }

    function removeMessageElement(message_id) {
        const messageElement = document.querySelector(`.message[data-message-id="${message_id}"]`);
        if (messageElement) {
            messageElement.remove();
        }
    }

    function deleteMessage(message_id) {
        fetch('messages.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ action: 'delete', message_id: message_id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ws.send(JSON.stringify({
                    type: 'delete',
                    room_id: roomSelect.value,
                    message_id: message_id
                }));
                removeMessageElement(message_id);
            } else {
                console.error('Error:', data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function fetchMessages(roomId) {
        fetch(`messages.php?room_id=${roomId}`)
        .then(response => response.json())
        .then(data => {
            chatBox.innerHTML = '';
            data.forEach(message => {
                const messageElement = createMessageElement(message);
                chatBox.appendChild(messageElement);
            });
            chatBox.scrollTop = chatBox.scrollHeight;
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function updateRoomList() {
        fetch('rooms.php')
        .then(response => response.json())
        .then(data => {
            roomSelect.innerHTML = '';
            data.forEach(room => {
                const option = document.createElement('option');
                option.value = room.id;
                option.textContent = room.name;
                roomSelect.appendChild(option);
            });
            fetchMessages(roomSelect.value);
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    roomSelect.addEventListener("change", () => {
        ws.send(JSON.stringify({ type: 'join', room_id: roomSelect.value }));
        fetchMessages(roomSelect.value);
    });

    updateRoomList();
});
