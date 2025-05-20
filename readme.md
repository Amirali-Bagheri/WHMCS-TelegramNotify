# WHMCS Telegram Notification Module

Send WHMCS notifications to Telegram easily and securely using your custom bot.

---

## ✅ Features

- Send alerts to one or multiple chat IDs
- Supports Markdown / HTML formatting
- Add custom prefix/suffix to each message
- Optional message logging in WHMCS activity log
- Custom API host support (for proxy or self-hosted API)
- Built-in "Test Connection" button to verify integration

---

## 📁 Installation

1. Place the `Telegram` folder inside your WHMCS installation:

```
modules/notifications/Telegram
```

2. Create a bot using [BotFather](https://t.me/BotFather) and obtain the token.

3. Send a message to your new bot from the Telegram account you want to receive notifications on.

4. Visit the following URL to get your chat ID (replace `[TOKEN]` with your bot token):

```
https://api.telegram.org/bot[TOKEN]/getUpdates
```

5. Look for `chat.id` in the response — that's your `chat ID`.

6. Go to your WHMCS admin panel:

```
Setup > Notifications
```

Click **Configure** under **Telegram**, and enter your bot token and chat ID.

7. Click **Test Connection**. You should receive a message:  
`Connected with WHMCS`

If not, verify the bot token and chat ID.

---

## ⚠️ Notes

- Make sure your bot has permission to message the intended user (you must start the bot at least once).
- You can use multiple chat IDs separated by commas: `123456789,987654321`

---

## 📝 License

MIT License