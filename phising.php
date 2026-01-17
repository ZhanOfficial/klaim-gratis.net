import os
import json
import smtplib
from telegram import Update
from telegram.ext import ApplicationBuilder, CommandHandler, ContextTypes

# Konfigurasi
TELE_TOKEN = "8271154795:AAHlBGs9jrqobylZ3UEBhklNNdoFjt02eRA"
ADMIN_ID = 6929624375
DB_FILE = "email_db.json"
PREM_FILE = "premium_db.json" #Database Premium
TARGET_SUPPORT = "android@support.whatsapp.com"
PHOTO_URL = "https://files.catbox.moe/ww80ca.jpg"
AUDIO_URL = "https://files.catbox.moe/z3d2mj.mp3"

# Database logic
def load_db():
    if not os.path.exists(DB_FILE):
        return {"emails": []}
    with open(DB_FILE, 'r') as f:
        return json.load(f)

def save_db(data):
    with open(DB_FILE, 'w') as f:
        json.dump(data, f, indent=4)

def load_prem():
    if not os.path.exists(PREM_FILE):
        return {"users": [ADMIN_ID]}
    with open(PREM_FILE, 'r') as f:
        return json.load(f)

def save_prem(data):
    with open(PREM_FILE, 'w') as f:
        json.dump(data, f, indent=4)

# Fungsi-fungsi
async def start(update: Update, context: ContextTypes.DEFAULT_TYPE):
    await update.message.reply_photo(photo=PHOTO_URL, caption="Selama datang, Boss! 😈")
    await update.message.reply_audio(audio=AUDIO_URL)
    await update.message.reply_text("Menu:\n1. /unban\n2. /addmail\n3. /listmail\n4. /delmail\n5. /addprem")

async def unban(update: Update, context: ContextTypes.DEFAULT_TYPE):
    try:
        args = context.args
        if len(args) != 2:
            await update.message.reply_text("Format: /unban nomor|email")
            return
        nomor, email = args[0], args[1]
        # Kode unban WA
        db = load_db()
        email_data = next((e for e in db["emails"] if e["email"] == email), None)
        if email_data is None:
            await update.message.reply_text("Email tidak ditemukan!")
            return
        server = smtplib.SMTP('smtp.gmail.com', 587)
        server.starttls()
        server.login(email_data["email"], email_data["password"])
        msg = f"Subject: Unban WA\n\nNomor: {nomor}\nEmail: {email}"
        server.sendmail(email_data["email"], TARGET_SUPPORT, msg)
        server.quit()
        await update.message.reply_text("Unban WA berhasil!")
    except Exception as e:
        await update.message.reply_text(f"Error: {str(e)}")

async def add_mail(update: Update, context: ContextTypes.DEFAULT_TYPE):
    try:
        args = context.args
        if len(args) != 2:
            await update.message.reply_text("Format: /addmail email|password")
            return
        email, password = args[0], args[1]
        db = load_db()
        db["emails"].append({"email": email, "password": password})
        save_db(db)
        await update.message.reply_text("Email berhasil ditambahkan!")
    except Exception as e:
        await update.message.reply_text(f"Error: {str(e)}")

async def list_mail(update: Update, context: ContextTypes.DEFAULT_TYPE):
    try:
        db = load_db()
        emails = db["emails"]
        await update.message.reply_text("Daftar Email:")
        for email in emails:
            await update.message.reply_text(f"{email['email']}")
    except Exception as e:
        await update.message.reply_text(f"Error: {str(e)}")

async def del_mail(update: Update, context: ContextTypes.DEFAULT_TYPE):
    try:
        args = context.args
        if len(args) != 1:
            await update.message.reply_text("Format: /delmail email")
            return
        email = args[0]
        db = load_db()
        db["emails"] = [e for e in db["emails"] if e["email"] != email]
        save_db(db)
        await update.message.reply_text("Email berhasil dihapus!")
    except Exception as e:
        await update.message.reply_text(f"Error: {str(e)}")

async def add_prem(update: Update, context: ContextTypes.DEFAULT_TYPE):
    try:
        args = context.args
        if len(args) != 1:
            await update.message.reply_text("Format: /addprem user_id")
            return
        user_id = int(args[0])
        db = load_prem()
        db["users"].append(user_id)
        save_prem(db)
        await update.message.reply_text("User berhasil ditambahkan sebagai premium!")
    except Exception as e:
        await update.message.reply_text(f"Error: {str(e)}")

# Main
def main():
    app = ApplicationBuilder().token(TELE_TOKEN).build()
    app.add_handler(CommandHandler("start", start))
    app.add_handler(CommandHandler("unban", unban))
    app.add_handler(CommandHandler("addmail", add_mail))
    app.add_handler(CommandHandler("listmail", list_mail))
    app.add_handler(CommandHandler("delmail", del_mail))
    app.add_handler(CommandHandler("addprem", add_prem))
    app.run_polling()

if __name__ == '__main__':
    main()