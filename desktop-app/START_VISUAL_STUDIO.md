# Arma Battles Chat - Desktop App för Windows

## 🚀 Snabbstart med Visual Studio 2022

### Krav
- **Visual Studio 2022** (Community, Professional eller Enterprise)
  - Med workload: "Node.js development"
- **Node.js** 18+ (https://nodejs.org)
- **pnpm** (kör `corepack enable` i terminal)
- **Git** (för version control)

---

## 📦 Installation

### 1. Öppna projektet i Visual Studio

**Alternativ A: Använd Solution-filen**
```bash
# Dubbelklicka på:
C:\revolt\desktop-app\ArmaBattlesChat.sln
```

**Alternativ B: Öppna mappen**
```
File → Open → Folder → Välj C:\revolt\desktop-app
```

### 2. Installera dependencies

Öppna **Terminal** i Visual Studio (View → Terminal) och kör:

```bash
# Aktivera pnpm
corepack enable

# Installera alla paket
pnpm install --frozen-lockfile
```

---

## 🏃 Köra appen

### Development Mode (med hot reload)

```bash
# Starta appen i development mode
pnpm start

# Eller connecta till lokal dev-server
pnpm start -- --force-server http://localhost:5173
```

### Production Build

```bash
# Bygg Windows installer (.exe)
pnpm make

# Färdig installer finns i:
# desktop-app/out/make/squirrel.windows/x64/
```

---

## 🛠️ Visual Studio Tasks

### Debug Configuration

1. Gå till **Debug → Start Debugging** (F5)
2. Välj "Node.js" som debug environment
3. Appen startar med debugger attached

### Build Tasks

I **Terminal** (Ctrl + `) kör du:

| Kommando | Beskrivning |
|----------|-------------|
| `pnpm start` | Starta appen i dev mode |
| `pnpm package` | Bygg appen (utan installer) |
| `pnpm make` | Bygg full installer för Windows |
| `pnpm lint` | Kör ESLint |

---

## 📁 Projekt Struktur

```
desktop-app/
├── src/
│   ├── main.ts           # Electron main process
│   ├── preload.ts        # Preload script (security bridge)
│   ├── renderer.ts       # Renderer process
│   ├── native/           # Native integrations
│   │   ├── window.ts     # Fönsterhantering
│   │   ├── tray.ts       # System tray icon
│   │   ├── config.ts     # App configuration
│   │   └── discordRpc.ts # Discord Rich Presence
│   └── world/            # Isolated world scripts
├── assets/               # Logos, ikoner (lägg dina här!)
├── forge.config.ts       # Electron Forge config
├── package.json          # Dependencies & scripts
└── tsconfig.json         # TypeScript config
```

---

## 🎨 Branding Setup

### 1. Uppdatera App-namn

Redigera `package.json`:
```json
{
  "name": "arma-battles-chat",
  "productName": "Arma Battles Chat",
  "version": "1.0.0"
}
```

### 2. Uppdatera Forge Config

Redigera `forge.config.ts` (rad 15-20):
```typescript
const STRINGS = {
  author: "Arma Battles",
  name: "Arma Battles Chat",
  execName: "arma-battles-chat",
  description: "Official chat platform for Arma Battles community.",
};
```

### 3. Lägg till Logotyper

Placera dina assets i `assets/desktop/`:
```
assets/desktop/
├── icon.ico       # Windows icon (256x256)
├── icon.png       # Linux icon (512x512)
└── icon.icns      # Mac icon (optional)
```

### 4. Uppdatera Backend URL

Redigera `src/native/window.ts`:
```typescript
// Hitta BUILD_URL och ändra till:
export const BUILD_URL = "https://chat.armabattles.com";
```

### 5. Uppdatera App ID

Redigera `src/main.ts` (rad 47):
```typescript
app.setAppUserModelId("com.armabattles.chat");
```

---

## 🧪 Testa Builden

### Lokal Test

```bash
# 1. Bygg appen
pnpm package

# 2. Kör den byggda versionen
.vite/build/main.js
```

### Installer Test

```bash
# 1. Bygg installer
pnpm make

# 2. Hitta .exe i:
out/make/squirrel.windows/x64/arma-battles-chat-setup.exe

# 3. Kör installern och testa!
```

---

## 🐛 Debugging

### Visual Studio Debugger

1. Sätt breakpoints i `.ts` filer
2. Tryck **F5** för att starta med debugger
3. Debuggern stannar vid breakpoints

### Electron DevTools

I appen:
- Tryck **F12** för att öppna DevTools
- **Ctrl+Shift+I** öppnar även DevTools
- **Ctrl+R** reload appen

### Loggar

```bash
# Electron main process logs
console.log("Main:", ...);

# Renderer process logs (i DevTools Console)
console.log("Renderer:", ...);
```

---

## 📦 Distribution

### Windows Installer

```bash
pnpm make
```

Skapar:
- `arma-battles-chat-setup.exe` - Full installer med auto-update
- `RELEASES` - Update metadata
- `.nupkg` filer - Update packages

### Auto-Update

Appen använder `update-electron-app` för automatiska uppdateringar via GitHub Releases.

---

## ⚡ Keyboard Shortcuts i Visual Studio

| Shortcut | Funktion |
|----------|----------|
| **F5** | Start Debugging |
| **Ctrl + F5** | Start Without Debugging |
| **Ctrl + `** | Toggle Terminal |
| **Ctrl + Shift + B** | Build |
| **Ctrl + K, Ctrl + C** | Comment Selection |
| **Ctrl + K, Ctrl + U** | Uncomment Selection |
| **F12** | Go to Definition |
| **Ctrl + -** | Navigate Backward |

---

## 🔗 Länkar

- [Electron Documentation](https://www.electronjs.org/docs)
- [Electron Forge](https://www.electronforge.io/)
- [TypeScript Handbook](https://www.typescriptlang.org/docs/)
- [Visual Studio Node.js](https://learn.microsoft.com/en-us/visualstudio/javascript/nodejs-overview)

---

## ✅ Checklist för Release

- [ ] Uppdaterat `package.json` med Arma Battles info
- [ ] Uppdaterat `forge.config.ts` med branding
- [ ] Lagt till logos i `assets/desktop/`
- [ ] Ändrat backend URL till `chat.armabattles.com`
- [ ] Testat appen i dev mode (`pnpm start`)
- [ ] Byggt och testat installer (`pnpm make`)
- [ ] Verifierat auto-update fungerar
- [ ] Testat system tray icon
- [ ] Verifierat Discord Rich Presence (om aktivt)

---

**Lycka till med utvecklingen! 🎮**
