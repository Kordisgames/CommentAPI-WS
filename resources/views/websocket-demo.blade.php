<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Демо веб-сокетов</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .notification {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
            animation: slideIn 0.5s ease-out;
        }
        @keyframes slideIn {
            from { transform: translateX(-100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .notification-title {
            font-weight: bold;
            color: #1976d2;
            margin-bottom: 5px;
        }
        .notification-content {
            color: #333;
            margin: 5px 0;
        }
        .notification-meta {
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Демонстрация веб-сокетов</h1>
        <div id="notifications">
            <!-- Здесь будут появляться уведомления -->
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ws = new WebSocket(`ws://${window.location.hostname}:6001`);

            ws.onopen = () => {
                console.log('WebSocket подключен');
            };

            ws.onmessage = (event) => {
                console.log('Получено сообщение:', event.data);

                try {
                    const data = JSON.parse(event.data);
                    console.log('Распарсенные данные:', data);

                    if (data.event === 'CommentCreated') {
                        console.log('Обработка события CommentCreated:', data.data);
                        const comment = data.data.comment;
                        const notifications = document.getElementById('notifications');

                        const notification = document.createElement('div');
                        notification.className = 'notification';

                        notification.innerHTML = `
                            <div class="notification-title">Новый комментарий к новости #${comment.news_id}</div>
                            <div class="notification-content">${comment.content}</div>
                            <div class="notification-meta">
                                Автор: ${comment.user.name} •
                                ${new Date(comment.created_at).toLocaleString('ru-RU')}
                            </div>
                        `;

                        notifications.insertBefore(notification, notifications.firstChild);

                        // Удаляем уведомление через 5 секунд
                        setTimeout(() => {
                            notification.style.opacity = '0';
                            notification.style.transition = 'opacity 0.5s ease-out';
                            setTimeout(() => notification.remove(), 500);
                        }, 5000);
                    } else {
                        console.log('Неизвестное событие:', data.event);
                    }
                } catch (e) {
                    console.error('Ошибка при обработке сообщения:', e);
                }
            };

            ws.onclose = () => {
                console.log('WebSocket отключен');
                // Пробуем переподключиться через 5 секунд
                setTimeout(() => location.reload(), 5000);
            };

            ws.onerror = (error) => {
                console.error('Ошибка WebSocket:', error);
            };
        });
    </script>
</body>
</html>