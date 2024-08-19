document.addEventListener("DOMContentLoaded", () => {
    const messageForm = document.getElementById("message-form");
    const chatBox = document.getElementById("chat-box");
    const roomSelect = document.getElementById("room-select");
    const recordButton = document.getElementById("record-button");
    const ws = new WebSocket('wss://healthtech.ezems.co.ke:443');
    const userId = document.body.getAttribute('data-user-id');
    const recordingIndicator = document.getElementById('recording-indicator');
    let mediaRecorder;
    let audioChunks = [];

    // Initialize Howler.js
    var sound = new Howl({
        src: ['../hms/admin/assets/ezems.wav'], // Ensure this path is correct
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

    ws.onerror = function(error) {
        console.error('WebSocket Error:', error);
        alert('An error occurred with the WebSocket connection. Please try again later.');
    };

    ws.onclose = function() {
    console.log('WebSocket connection closed');
    alert('Connection lost. Attempting to reconnect...');

    // Attempt to reconnect after a delay
    setTimeout(function() {
        ws = new WebSocket('wss://www.healthtech.ezems.co.ke:443');
    }, 5000); // Reconnect after 5 seconds
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

        return audioElement;
    }

    function removeMessageElement(message_id) {
        const messages = document.querySelectorAll('.message');
        messages.forEach(messageElement => {
            if (messageElement.dataset.messageId === message_id.toString()) {
                messageElement.remove();
            }
        });
    }

    function deleteMessage(message_id) {
        fetch('messages.php', { // Ensure the correct path to messages.php
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ action: 'delete', message_id: message_id })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                ws.send(JSON.stringify({
                    type: 'delete',
                    room_id: roomSelect.value,
                    message_id: message_id
                }));
            } else {
                console.error('Error:', data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete message. Please try again.');
        });
    }

    function fetchMessages(roomId) {
        fetch(`messages.php?room_id=${roomId}`) // Ensure the correct path to messages.php
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                chatBox.innerHTML = ''; // Clear the chat box
                data.messages.forEach(message => {
                    const messageElement = message.type === 'audio' ?
                        createAudioElement(message) :
                        createMessageElement(message);
                    chatBox.appendChild(messageElement);
                });
                chatBox.scrollTop = chatBox.scrollHeight; // Scroll to the bottom of the chat box
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to fetch messages. Please try again.');
            });
    }

    roomSelect.addEventListener("change", function() {
        ws.send(JSON.stringify({ type: 'join', room_id: roomSelect.value }));
        fetchMessages(roomSelect.value);
    });

    // Fetch initial messages for the current room
    fetchMessages(roomSelect.value);
});
