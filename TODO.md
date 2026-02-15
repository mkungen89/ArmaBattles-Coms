# Arma Battles Chat - TODO List

> **⚠️ IMPORTANT:** This platform is based on Revolt/Stoat Chat. The upstream project has **182+ open issues**, many of which affect us. Priority 2 includes critical bugs inherited from upstream that need fixing.

## 📊 Quick Stats
- **Platform:** Revolt-based chat (forked architecture)
- **Upstream Issues:** 182+ open (as of 2026-02-15)
- **Critical Bugs Inherited:** ~30+ affecting core functionality
- **OAuth Status:** Configured but Laravel endpoints needed
- **Voice/Video:** Disabled (VITE_ENABLE_VOICE=false)
- **Production Status:** Development only (not deployed)

---

## 🎯 Priority 1 - Critical Features

### OAuth Integration with Laravel
- [ ] **Create Laravel OAuth endpoints** on armabattles.com
  - [ ] `GET /oauth/authorize` - Authorization page with user consent
  - [ ] `POST /oauth/token` - Token exchange endpoint
  - [ ] `GET /oauth/user` - User info endpoint
  - [ ] Store OAuth clients in database
  - [ ] Implement PKCE (Proof Key for Code Exchange) for security
- [ ] **Configure OAuth Client Secret** in backend `.env`
  - Current Client ID: `019c5d06-b3f3-709a-a212-b4441d609080`
  - Set `OAUTH_CLIENT_SECRET` environment variable
- [ ] **Test OAuth flow** end-to-end
  - [ ] Login via armabattles.com
  - [ ] Token exchange
  - [ ] User profile sync
  - [ ] Session management
- [ ] **User data mapping** Laravel → Chat
  ```json
  {
    "id": "user-id",
    "email": "user@example.com",
    "name": "Username",
    "avatar": "https://..."
  }
  ```

### Backend Deployment
- [ ] **Set up production backend server**
  - [ ] Deploy to VPS/cloud server
  - [ ] Configure SSL certificates (Let's Encrypt)
  - [ ] Set up reverse proxy (Nginx)
  - [ ] Configure production environment variables
  - [ ] Set up MongoDB in production
  - [ ] Set up Redis in production
  - [ ] Configure MinIO/S3 for file storage
  - [ ] Set up RabbitMQ for messaging queue
- [ ] **Domain configuration**
  - [ ] Point `chat.armabattles.com` to server
  - [ ] Configure DNS records
  - [ ] SSL certificate for chat.armabattles.com
- [ ] **Backend services**
  - [ ] delta (REST API) on port 14702
  - [ ] bonfire (WebSocket) on port 14703
  - [ ] autumn (File Server) on port 14704
  - [ ] january (Proxy) on port 14705
  - [ ] gifbox (Tenor Proxy) on port 14706

### Frontend Deployment
- [ ] **Build production frontend**
  - [ ] Update .env.production with correct API URLs
  - [ ] Build optimized bundle
  - [ ] Deploy to chat.armabattles.com
  - [ ] Configure Nginx to serve static files
  - [ ] Set up PWA service worker
- [ ] **Update environment variables**
  ```
  VITE_API_URL=https://chat.armabattles.com/api
  VITE_WS_URL=wss://chat.armabattles.com/ws
  VITE_OAUTH_ENABLED=true
  VITE_OAUTH_PROVIDER_NAME=Arma Battles
  VITE_OAUTH_CLIENT_ID=019c5d06-b3f3-709a-a212-b4441d609080
  VITE_OAUTH_REDIRECT_URI=https://chat.armabattles.com/auth/callback
  ```

---

## 🐛 Priority 2 - Bug Fixes & Code Quality

### Critical Issues from Upstream (stoatchat/for-web)
*Based on 182+ open issues in the Revolt fork - many affect our platform too*

**UI/Rendering Bugs:**
- [ ] **Fix flickering UI** (#750, #752)
  - General interface flickering affecting user experience
  - Font selector flickering specifically
  - Check CSS animations and re-renders
- [ ] **2FA QR code missing white margin** (#754)
  - QR codes unreadable by some scanner apps
  - Add white border/padding around QR code
  - Location: `arma-frontend/src/controllers/modals/components/MFAEnableTOTP.tsx`
- [ ] **Monochrome theme text readability** (#588)
  - Inconsistent font colors making text hard to read
  - Review all theme CSS for contrast
- [ ] **Message timestamp wrapping** (#717)
  - Creates extra unwanted space
  - Fix flexbox/grid layout
- [ ] **Message box no maximum height** (#741)
  - Can cause performance issues with very long messages
  - Add max-height with scroll

**Authentication & Profile:**
- [ ] **Email verification shown incorrectly** (#759)
  - Verification page appears regardless of server config
  - Check backend email verification settings
- [ ] **Profile photo upload broken** (#756, #644)
  - Users unable to save profile pictures
  - Check file upload to MinIO/S3
  - Verify backend endpoint
- [ ] **Theme sync on login triggers unauthorized error** (#746)
  - Changing theme attempts sync without proper auth
  - Fix auth check timing

**Channel/Message Issues:**
- [ ] **Channels don't load after certain actions** (#767)
  - Investigate loading state management
  - Check WebSocket reconnection logic
- [ ] **Deleting invite causes list to disappear** (#763)
  - State update bug when removing invites
  - Fix list re-render logic
- [ ] **Permission ordering allows self-hiding** (#642)
  - Users can hide channels from themselves via permissions
  - Add validation to prevent this
- [ ] **Can't mention self** (#765)
  - Self-mention should be allowed
  - Fix mention validation logic

**Voice/Call Problems:**
- [ ] **Voice calls block message visibility** (#748)
  - Call UI overlaps message area
  - Add minimize/toggle for call card
- [ ] **Cannot join voice calls** (#659)
  - Users unable to join voice/video
  - Check WebRTC connection flow
  - Related to disabled voice: VITE_ENABLE_VOICE=false
- [ ] **Call card always visible** (#541)
  - Need option to hide "Join Call" button
  - Add user preference setting

**Settings & State:**
- [ ] **"Start with Computer" won't stay disabled** (#549, #643, #679)
  - Settings persistence bug (multiple reports)
  - Check localStorage/settings sync
  - Location: `arma-frontend/src/pages/settings/panes/Native.tsx`
- [ ] **Version info outdated** (#745)
  - Settings page shows wrong version
  - Update version display logic
- [ ] **Status updates don't reflect immediately** (#744)
  - Self presence changes not shown in UI
  - Fix real-time status sync

**Friends & Social:**
- [ ] **Friend requests don't appear** (#730)
  - Added friend not visible in list
  - Check friend list sync/refresh
- [ ] **Cannot delete bots** (#724)
  - Critical functionality missing
  - Add bot deletion endpoint and UI

**Accessibility:**
- [ ] **hCaptcha blocks screen readers** (#727)
  - Sign-up inaccessible with assistive tech
  - Implement accessible CAPTCHA alternative
  - Consider audio CAPTCHA or alternative verification
- [ ] **GIF/emoji autoplay no disable option** (#664)
  - Accessibility concern for some users
  - Add setting to disable animations
  - Respect prefers-reduced-motion

**Code/Display:**
- [ ] **Lua syntax highlighting outdated** (#742)
  - Update code highlighting library
  - Check other language support too

**Layout Issues:**
- [ ] **General chat floats over menus** (#704)
  - Z-index/layering problem
  - Fix CSS stacking context

### Critical Backend Issues
- [ ] **Fix 119+ `.unwrap()` panic points** in backend
  - Location: `arma-backend/crates/delta/src/routes/`
  - Replace with proper error handling
  - Return user-friendly error messages
- [ ] **Implement missing kick functionality**
  - System message exists (UserKicked) but endpoint missing
  - Add `/servers/{server}/members/{member}/kick` endpoint
- [ ] **Add timeout/mute features**
  - Create timeout model in database
  - Add mute duration field
  - Implement auto-unmute timer
  - Add UI for timeout management

### Frontend Issues
- [ ] **VoiceState.ts error notifications** (Lines 155, 166, 181, 184)
  - Add user-facing error messages for voice operations
  - Implement toast notifications
- [ ] **ContextMenus.tsx cleanup**
  - Line 118: Rewrite complex context menu logic
  - Line 289, 301: Move operations to revolt.js
  - Line 323: Add modal for delete confirmation
  - Line 974: Implement group invite support
- [ ] **MyBots.tsx rewrite** (Line 1)
  - Refactor bot management UI
  - Improve code quality
- [ ] **Performance fixes in MemberSidebar.tsx** (Lines 150, 190)
  - Implement lazy loading for guild members
  - Add virtualization for large member lists
  - Optimize rendering performance
- [ ] **Add missing translations**
  - UserProfile modal (Lines 206, 318, 335)
  - NotificationOptions (Line 325)
  - Various UI strings

### Type Safety
- [ ] **Clean up TypeScript types** in revolt.js integration
  - Remove @ts-expect-error comments
  - Add proper type definitions
  - Fix type mismatches

---

## ✨ Priority 3 - Feature Enhancements

### Voice & Video (Currently Disabled)
- [ ] **Enable voice chat** (VITE_ENABLE_VOICE=false)
  - Complete VoiceClient.ts implementation
  - Add WebRTC integration
  - Implement peer-to-peer voice
  - Add voice channel UI
  - Test voice quality
- [ ] **Enable video chat** (VITE_ENABLE_VIDEO=false)
  - Add video stream support
  - Camera/microphone permissions
  - Video UI components
- [ ] **Screen sharing**
  - Implement screen capture API
  - Add screen share controls
  - Quality settings

### Advanced Communication Features
- [ ] **Threads system**
  - Create Thread model in database
  - Thread creation UI
  - Thread listing/navigation
  - Thread archiving
  - Unread thread indicators
- [ ] **Forums**
  - Forum channel type
  - Post model with tags
  - Sorting (latest, trending, pinned)
  - Forum moderation tools
- [ ] **Message scheduling**
  - Schedule message UI
  - Background job for scheduled sends
  - Edit/cancel scheduled messages

### Moderation Tools
- [ ] **Advanced moderation**
  - Slowmode for channels
  - Auto-moderation rules
  - Word filters
  - Spam detection
  - Raid protection
- [ ] **Audit logs**
  - Comprehensive action logging
  - Audit log viewer UI
  - Filter by action type/user
  - Export audit logs
- [ ] **Moderation dashboard**
  - Active warnings/bans overview
  - Moderation queue
  - Reports management
  - Analytics

### Server Features
- [ ] **Server templates**
  - Save server as template
  - Apply template on creation
  - Template marketplace
- [ ] **Server analytics**
  - Member growth charts
  - Activity metrics
  - Popular channels
  - Engagement statistics
- [ ] **Enhanced discovery**
  - Server categories
  - Search filters
  - Featured servers
  - Server previews

---

## 🎨 Priority 4 - Branding & UI/UX

### Remaining Branding Updates
- [ ] **Update language files** (135+ files)
  - Location: `arma-frontend/external/lang/*.json`
  - Replace all "Revolt" references with "Arma Battles"
  - Update translations for all languages
- [ ] **Update changelog files**
  - `arma-frontend/src/assets/changelogs.tsx` (Lines 50, 69)
  - Historical changelog entries
- [ ] **Update emoji attribution**
  - `EmojiSelector.tsx` Line 110 - "(by Revolt)" → "(by Arma Battles)"
  - Update link to custom emoji set
- [ ] **Update GitHub templates**
  - `.github/pull_request_template.md` Line 5
  - `.github/ISSUE_TEMPLATE/bug.yml` (Lines 21, 23, 57)
- [ ] **Error reporting URL**
  - Update `ErrorBoundary.tsx` Line 42
  - Change from `https://reporting.revolt.chat` to Arma Battles reporting

### UI Improvements
- [ ] **Notification system enhancements**
  - Make muted channels gray (NotificationOptions.ts Line 18)
  - Add server notification defaults (Line 19)
- [ ] **Server sidebar improvements**
  - Move sidebar code globally (ServerSidebar.tsx Lines 60, 63, 68)
  - Consistent navigation patterns
- [ ] **Mobile responsiveness**
  - Test on mobile browsers
  - Optimize touch interactions
  - Improve mobile navigation

### Visual Polish
- [ ] **Custom theme support**
  - Theme editor UI
  - Color customization
  - Theme import/export
- [ ] **Animation improvements**
  - Smooth transitions
  - Loading states
  - Micro-interactions
- [ ] **Accessibility**
  - ARIA labels
  - Keyboard navigation
  - Screen reader support
  - High contrast mode

---

## 📱 Priority 5 - Mobile & Desktop Apps

### Mobile Applications
- [ ] **iOS App**
  - React Native implementation
  - Push notifications
  - Native camera/file picker
  - App Store submission
- [ ] **Android App**
  - React Native implementation
  - Push notifications
  - Native integrations
  - Play Store submission

### Desktop Application
- [ ] **Electron App** improvements
  - Update "Arma Battles for Desktop" branding
  - Native notifications
  - System tray integration
  - Auto-updates
  - Window management
  - Deep linking

---

## 🔧 Priority 6 - DevOps & Infrastructure

### Monitoring & Logging
- [ ] **Set up monitoring**
  - Server health monitoring
  - Database performance
  - API response times
  - Error tracking (Sentry/similar)
  - Uptime monitoring
- [ ] **Logging infrastructure**
  - Centralized logging (ELK stack)
  - Log rotation
  - Error aggregation
  - Performance metrics

### Backup & Recovery
- [ ] **Database backups**
  - Automated MongoDB backups
  - Backup retention policy
  - Restore procedures
  - Test recovery process
- [ ] **File storage backups**
  - MinIO/S3 backup strategy
  - Media file retention
  - CDN integration

### Performance Optimization
- [ ] **Frontend optimization**
  - Code splitting
  - Lazy loading
  - Image optimization
  - CDN for assets
- [ ] **Backend optimization**
  - Database indexing
  - Query optimization
  - Caching strategy (Redis)
  - Connection pooling
- [ ] **WebSocket optimization**
  - Connection management
  - Message batching
  - Reconnection strategy

### CI/CD Pipeline
- [ ] **Automated testing**
  - Unit tests
  - Integration tests
  - E2E tests
- [ ] **Deployment automation**
  - GitHub Actions workflow
  - Automated builds
  - Staging environment
  - Production deployment
- [ ] **Code quality**
  - ESLint configuration
  - Prettier formatting
  - Pre-commit hooks
  - Code review process

---

## 🔒 Priority 7 - Security & Compliance

### Security Enhancements
- [ ] **Security audit**
  - Penetration testing
  - Vulnerability scanning
  - Dependency updates
  - Security headers
- [ ] **Rate limiting improvements**
  - IP-based rate limiting
  - User-based limits
  - DDoS protection
  - Cloudflare integration
- [ ] **Data encryption**
  - End-to-end encryption for DMs (optional)
  - Encrypted file storage
  - Secure token storage

### Compliance
- [ ] **Privacy policy**
  - Create privacy policy page
  - GDPR compliance
  - Data retention policy
  - User data export
  - Right to deletion
- [ ] **Terms of service**
  - Platform rules
  - Acceptable use policy
  - Content moderation guidelines
- [ ] **Cookie consent**
  - Cookie banner
  - Cookie preferences
  - Analytics opt-out

---

## 📚 Priority 8 - Documentation

### User Documentation
- [ ] **User guide**
  - Getting started
  - Creating servers
  - Managing roles
  - Using bots
  - Privacy settings
- [ ] **FAQ page**
  - Common questions
  - Troubleshooting
  - Feature explanations
- [ ] **Video tutorials**
  - Platform walkthrough
  - Server setup guide
  - Moderation tutorial

### Developer Documentation
- [ ] **API documentation**
  - REST API endpoints
  - WebSocket events
  - Authentication flow
  - Rate limits
  - Examples
- [ ] **Bot development guide**
  - Bot creation tutorial
  - API libraries
  - Best practices
  - Example bots
- [ ] **Self-hosting guide**
  - Installation steps
  - Configuration options
  - Docker deployment
  - Troubleshooting

### Internal Documentation
- [ ] **Architecture documentation**
  - System design
  - Database schema
  - Service interactions
  - Deployment architecture
- [ ] **Development setup**
  - Local environment setup
  - Contribution guidelines
  - Code style guide
  - Git workflow

---

## 🎮 Priority 9 - Gaming Integration

### Arma-Specific Features
- [ ] **Game server integration**
  - Server status display
  - Player count
  - Map rotation
  - Join server button
- [ ] **Match notifications**
  - Tournament alerts
  - Match reminders
  - Score updates
  - Live match updates
- [ ] **Team management**
  - Team channels
  - Roster management
  - Practice scheduling
  - Statistics display
- [ ] **Rich presence**
  - Current game status
  - Server playing on
  - Playtime tracking
  - Achievement display

### Bot Features
- [ ] **Arma Battles bot**
  - Server commands
  - User stats lookup
  - Tournament management
  - Leaderboards
  - Match scheduling
- [ ] **Moderation bot**
  - Auto-moderation
  - Warning system
  - Anti-spam
  - Raid protection

---

## 📊 Current Status Summary

### ✅ Completed
- [x] Rebranded from "Revolt" to "Arma Battles" (main UI)
- [x] Docker setup for local development
- [x] MongoDB, Redis, MinIO, RabbitMQ configured
- [x] OAuth configuration prepared for armabattles.com
- [x] Frontend build system working
- [x] Basic text chat functionality
- [x] Server/channel structure
- [x] Role-based permissions
- [x] File sharing (20MB limit)
- [x] Emoji and reactions
- [x] Bot support
- [x] Friend system
- [x] Web push notifications

### 🚧 In Progress
- [ ] Complete rebranding (language files pending)
- [ ] OAuth Laravel integration (endpoints needed)
- [ ] Production deployment (not yet deployed)

### ⏳ Not Started
- [ ] Voice/video features
- [ ] Threads and forums
- [ ] Mobile apps
- [ ] Advanced moderation
- [ ] Full documentation

---

## 📝 Notes

### Configuration Files to Update
- `arma-backend/.env` - Production environment variables
- `arma-frontend/.env.production` - Production API URLs
- `arma-backend/Revolt.production.toml` - Backend configuration
- `docker-compose.yml` - Production Docker setup

### Environment Variables Needed
```bash
# Backend
MONGODB_URI=mongodb://localhost:27017/armabattles
REDIS_URI=redis://localhost:6379/
OAUTH_CLIENT_SECRET=<secret-from-laravel>
S3_ENDPOINT=http://localhost:14009
JWT_SECRET=<random-secret>

# Frontend
VITE_API_URL=https://chat.armabattles.com/api
VITE_WS_URL=wss://chat.armabattles.com/ws
VITE_OAUTH_ENABLED=true
```

### Useful Commands
```bash
# Development
docker compose up -d              # Start all services
docker compose restart frontend   # Restart frontend
docker compose logs -f frontend   # View frontend logs

# Build
cd arma-frontend && docker build -t revolt-frontend .
cd arma-backend && cargo build --release

# Production
docker compose -f docker-compose.prod.yml up -d
```

---

## 🔗 Upstream Tracking

### Monitoring Upstream Issues
Since this platform is based on Revolt/Stoat Chat, we need to monitor their repositories for:
- Security vulnerabilities
- Critical bug fixes
- New features we might want to adopt
- Breaking changes

**Upstream Repositories:**
- **Frontend:** https://github.com/stoatchat/for-web (182+ open issues)
- **Backend:** https://github.com/revoltchat/backend
- **revolt.js:** https://github.com/revoltchat/revolt.js

**Issue References:**
When fixing bugs inherited from upstream, reference the original issue number (e.g., #750, #767) so we can:
1. Check if upstream has fixed it
2. Contribute fixes back to upstream
3. Track which issues affect us

**Contribution Strategy:**
- [ ] Set up process for contributing fixes back to upstream
- [ ] Monitor upstream releases for security patches
- [ ] Create custom fork tracking document
- [ ] Decide on merge strategy (cherry-pick vs full merge)

### Known Critical Upstream Issues Affecting Us
Based on review of 182+ open issues in stoatchat/for-web:

**High Priority (User-Facing):**
1. #767 - Channels don't load (CRITICAL)
2. #759 - Email verification always shown
3. #756/#644 - Profile photo upload broken
4. #750/#752 - UI flickering
5. #748 - Voice calls block messages

**Medium Priority (UX Issues):**
6. #763 - Delete invite breaks list
7. #754 - 2FA QR code unreadable
8. #746 - Theme sync auth error
9. #744 - Status updates delayed
10. #730 - Friend requests invisible

**Low Priority (Nice to Have):**
11. #742 - Lua syntax highlighting
12. #741 - Message box height
13. #727 - CAPTCHA accessibility
14. #717 - Timestamp wrapping
15. #664 - Autoplay disable option

### Patch Management
```bash
# Check for upstream updates
git remote add upstream https://github.com/stoatchat/for-web.git
git fetch upstream

# Review changes
git log HEAD..upstream/main

# Apply specific fixes (cherry-pick)
git cherry-pick <commit-hash>

# Or merge entire branch (risky)
git merge upstream/main
```

---

**Last Updated:** 2026-02-15
**Project:** Arma Battles Chat Platform
**Repository:** C:\revolt\
**Upstream Issues Reviewed:** 182+ (stoatchat/for-web)
