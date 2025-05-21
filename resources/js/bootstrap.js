import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Создаем простой WebSocket клиент
window.WebSocketClient = {
    connect: function(channel, callback) {
        const ws = new WebSocket(`ws://${window.location.hostname}:6001`);

        ws.onopen = () => {
            console.log('WebSocket подключен');
            // Подписываемся на канал
            ws.send(JSON.stringify({
                type: 'subscribe',
                channel: channel
            }));
        };

        ws.onmessage = (event) => {
            const data = JSON.parse(event.data);
            callback(data);
        };

        ws.onclose = () => {
            console.log('WebSocket отключен');
            // Пробуем переподключиться через 5 секунд
            setTimeout(() => this.connect(channel, callback), 5000);
        };

        return ws;
    }
};
