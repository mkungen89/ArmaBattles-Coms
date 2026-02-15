# Arma Battles Chat - Desktop App med VS Code

## 🚀 Snabbstart

### 1. Öppna i VS Code

```
1. Öppna VS Code
2. File → Open Folder
3. Välj: C:\revolt\desktop-app
```

Eller från PowerShell:
```powershell
cd C:\revolt\desktop-app
code .
```

---

## 📦 Installation

### 1. Installera pnpm (en gång)

**Öppna PowerShell som Administrator:**
```powershell
# Aktivera corepack
corepack enable

# Eller installera direkt via npm
npm install -g pnpm
```

### 2. Installera Dependencies

I VS Code Terminal (Ctrl + `):
```bash
pnpm install
```

---

## ▶️ Köra Appen

### Development Mode

**Alternativ 1: Via Terminal**
```bash
pnpm start
```

**Alternativ 2: Via Task Menu**
```
1. Tryck Ctrl + Shift + B
2. Välj "Start Desktop App"
```

**Alternativ 3: Med Debug**
```
1. Tryck F5
2. Debuggern startar appen automatiskt
```

### Connecta till Lokal Dev Server

```bash
pnpm start -- --force-server http://localhost:5173
```

---

## 🛠️ VS Code Tasks

Tryck **Ctrl + Shift + P** och skriv "Run Task":

| Task | Beskrivning |
|------|-------------|
| **Start Desktop App** | Kör appen i dev mode |
| **Build Windows Installer** | Bygg .exe installer |
| **Package App** | Bygg appen utan installer |
| **Install Dependencies** | Kör pnpm install |
| **Lint Code** | Kör ESLint |

Eller tryck **Ctrl + Shift + B** för att köra default task (Start Desktop App).

---

## 🐛 Debugging

### 1. Sätt Breakpoints

Klicka i marginalen bredvid radnummer i `.ts` filer.

### 2. Starta Debug

Tryck **F5** eller:
```
1. Gå till Debug panel (Ctrl + Shift + D)
2. Välj "Debug Main Process"
3. Tryck grön play-knapp
```

### Debug Både Main och Renderer

```
1. Debug panel → Välj "Debug Electron"
2. Tryck F5
```

---

## ⌨️ Keyboard Shortcuts

| Shortcut | Funktion |
|----------|----------|
| **Ctrl + `** | Toggle Terminal |
| **Ctrl + Shift + B** | Run Build Task |
| **F5** | Start Debugging |
| **Ctrl + Shift + D** | Debug Panel |
| **Ctrl + P** | Quick File Open |
| **Ctrl + Shift + P** | Command Palette |
| **F12** | Go to Definition |
| **Shift + F12** | Find References |
| **Ctrl + /** | Toggle Comment |

---

## 📁 Projekt Struktur

```
desktop-app/
├── .vscode/              # VS Code config (auto-setup!)
│   ├── launch.json       # Debug configurations
│   ├── tasks.json        # Build tasks
│   └── extensions.json   # Recommended extensions
├── src/
│   ├── main.ts          # Electron main process
│   ├── preload.ts       # Security bridge
│   ├── renderer.ts      # Renderer process
│   ├── native/
│   │   ├── window.ts    # Fönsterhantering
│   │   ├── tray.ts      # System tray
│   │   └── config.ts    # App config
│   └── world/           # Isolated contexts
├── assets/desktop/      # Logos & icons
├── forge.config.ts      # Build config
└── package.json         # Dependencies
```

---

## 📦 Build Commands

```bash
# Development
pnpm start                    # Starta dev mode
pnpm start -- --force-server http://localhost:5173

# Production
pnpm package                  # Bygg app
pnpm make                     # Bygg installer

# Quality
pnpm lint                     # ESLint
```

---

## 🎨 Branding Setup

### 1. Lägg Till Logotyper

Placera i `assets/desktop/`:
```
assets/desktop/
├── icon.ico      # Windows (256x256)
├── icon.png      # Linux (512x512)
└── icon.icns     # Mac (optional)
```

### 2. Testa Builden

```bash
# 1. Bygg installer
pnpm make

# 2. Hitta .exe
desktop-app/out/make/squirrel.windows/x64/arma-battles-chat-setup.exe

# 3. Kör och testa!
```

---

## 🔧 Rekommenderade Extensions

VS Code kommer föreslå dessa när du öppnar projektet:

- **ESLint** - Linting
- **Prettier** - Code formatting
- **TypeScript** - Type checking
- **Debugger for Chrome** - Renderer debugging

Installera genom att klicka på notifikationen eller:
```
Ctrl + Shift + P → "Show Recommended Extensions"
```

---

## 🐛 Troubleshooting

### "pnpm not found"

```powershell
# Öppna PowerShell som Admin
corepack enable

# Eller
npm install -g pnpm
```

### "Cannot find module"

```bash
# Radera och installera om
rm -rf node_modules
pnpm install
```

### Build Fails

```bash
# Kör clean install
pnpm install --frozen-lockfile
```

---

## ✅ Checklist

- [ ] Installerat VS Code
- [ ] Öppnat `desktop-app` mappen
- [ ] Installerat recommended extensions
- [ ] Kört `pnpm install`
- [ ] Startat appen med `pnpm start` eller F5
- [ ] Testat debug med breakpoints
- [ ] Byggt installer med `pnpm make`

---

**Nu är du redo att utveckla! 🎮**

Tryck **F5** för att starta med debugging, eller **Ctrl + Shift + B** för att bara köra appen!
