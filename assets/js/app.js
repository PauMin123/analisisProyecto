/**
 * ============================================================================
 * EnglishMadeForDummies - Application Controller (UI & Events)
 * Integración con Patrón Singleton (StudentSessionManager) y MySQL 'ingles'
 * ============================================================================
 */

import { StudentSessionManager } from './sessionManager.js';
import { CourseServiceFactory } from './courseFactory.js';

class AuthUIController {
  constructor() {
    this.sessionManager = StudentSessionManager.getInstance();
    this.courseFactory = CourseServiceFactory;
    window.CourseServiceFactory = CourseServiceFactory;
    window.StudentSessionManager = StudentSessionManager;
    this.apiUrl = 'api/auth.php';
    this.dashboardUrl = 'api/dashboard.php';
    this.initElements();
    this.initEvents();
    this.checkInitialSession();
    console.log("✅ EnglishMadeForDummies App Controller iniciado correctamente.");
  }

  initElements() {
    // Views & Containers
    this.viewLogin = document.getElementById('view-login');
    this.viewRegister = document.getElementById('view-register');
    this.viewDashboard = document.getElementById('view-dashboard');
    this.authContainer = document.getElementById('auth-form-container');

    // Forms & Buttons
    this.formLogin = document.getElementById('form-login');
    this.formRegister = document.getElementById('form-register');
    this.formRecover = document.getElementById('form-recover');
    this.btnSubmitLogin = document.getElementById('btn-submit-login');
    this.btnSubmitReg = document.getElementById('btn-submit-reg');

    // Switch Links
    this.linkGoToRegister = document.getElementById('link-go-to-register');
    this.linkGoToLogin = document.getElementById('link-go-to-login');

    // Password Toggles
    this.toggleLoginPass = document.getElementById('toggle-login-pass');
    this.inputLoginPass = document.getElementById('login-password');
    this.toggleRegPass = document.getElementById('toggle-reg-pass');
    this.inputRegPass = document.getElementById('reg-password');

    // Modal
    this.modalRecover = document.getElementById('modal-recover');
    this.btnOpenRecover = document.getElementById('btn-forgot-pass');
    this.btnCloseRecover = document.getElementById('btn-close-modal');

    // QR Modal
    this.modalQr = document.getElementById('modal-qr');
    this.btnShowQr = document.getElementById('btn-show-qr');
    this.btnCloseQr = document.getElementById('btn-close-qr');

    // Logout
    this.btnLogout = document.getElementById('btn-logout');

    // Toast Container
    this.toastContainer = document.getElementById('toast-container');

    // --- PANTALLA 3: AGENDAR LECCIÓN (Jira M01-11, M01-12) ---
    this.viewSchedule = document.getElementById('view-schedule');
    this.appBottomNav = document.getElementById('app-bottom-nav');
    this.btnBackSchedule = document.getElementById('btn-back-schedule');
    this.btnQuickSchedule = document.getElementById('btn-quick-schedule');

    // Bottom Navigation Items
    this.navDashboard = document.getElementById('nav-dashboard');
    this.navSchedule = document.getElementById('nav-schedule');
    this.navLessons = document.getElementById('nav-lessons');
    this.navProfile = document.getElementById('nav-profile');

    // Cascada Containers & Badges
    this.scheduleCoursesContainer = document.getElementById('schedule-courses-container');
    this.scheduleLevelsContainer = document.getElementById('schedule-levels-container');
    this.scheduleProfsContainer = document.getElementById('schedule-profs-container');
    this.levelsLockedPlaceholder = document.getElementById('levels-locked-placeholder');
    this.profsLockedPlaceholder = document.getElementById('profs-locked-placeholder');

    this.badgeStep1 = document.getElementById('badge-step-1');
    this.badgeStep2 = document.getElementById('badge-step-2');
    this.badgeStep3 = document.getElementById('badge-step-3');

    // Breadcrumb Steps
    this.chipStep1 = document.getElementById('chip-step-1');
    this.chipStep2 = document.getElementById('chip-step-2');
    this.chipStep3 = document.getElementById('chip-step-3');

    // Summary & Proceed
    this.scheduleSummaryBar = document.getElementById('schedule-summary-bar');
    this.summaryCourseName = document.getElementById('summary-course-name');
    this.summaryLevelName = document.getElementById('summary-level-name');
    this.summaryProfName = document.getElementById('summary-prof-name');
    this.btnProceedToCalendar = document.getElementById('btn-proceed-to-calendar');

    // --- PANTALLA 4: CALENDARIO Y SLOTS (Jira M01-13, M01-14) ---
    this.viewCalendar = document.getElementById('view-calendar');
    this.btnBackCalendar = document.getElementById('btn-back-calendar');
    this.calProfAvatar = document.getElementById('cal-prof-avatar');
    this.calProfName = document.getElementById('cal-prof-name');
    this.calCourseLevel = document.getElementById('cal-course-level');
    this.calProfRating = document.getElementById('cal-prof-rating');
    this.calendarDatesStrip = document.getElementById('calendar-dates-strip');
    this.calSelectedDayLabel = document.getElementById('cal-selected-day-label');
    this.calAvailableCount = document.getElementById('cal-available-count');
    this.slotsGridMorning = document.getElementById('slots-grid-morning');
    this.slotsGridAfternoon = document.getElementById('slots-grid-afternoon');
    this.slotsGridEvening = document.getElementById('slots-grid-evening');
    this.calendarConfirmBar = document.getElementById('calendar-confirm-bar');
    this.calConfirmCourse = document.getElementById('cal-confirm-course');
    this.calConfirmProf = document.getElementById('cal-confirm-prof');
    this.calConfirmTime = document.getElementById('cal-confirm-time');
    this.btnSubmitBooking = document.getElementById('btn-submit-booking');

    // --- PANTALLA 5: MIS LECCIONES (Jira M01-15, M01-16) ---
    this.viewLessons = document.getElementById('view-lessons');
    this.btnBackLessons = document.getElementById('btn-back-lessons');
    this.tabLessonsUpcoming = document.getElementById('tab-lessons-upcoming');
    this.tabLessonsHistory = document.getElementById('tab-lessons-history');
    this.countUpcoming = document.getElementById('count-upcoming');
    this.countHistory = document.getElementById('count-history');
    this.panelLessonsUpcoming = document.getElementById('panel-lessons-upcoming');
    this.panelLessonsHistory = document.getElementById('panel-lessons-history');

    // Modal de Cancelación de Lección
    this.modalCancelLesson = document.getElementById('modal-cancel-lesson');
    this.cancelProfName = document.getElementById('cancel-prof-name');
    this.cancelLessonDate = document.getElementById('cancel-lesson-date');
    this.cancelLessonTime = document.getElementById('cancel-lesson-time');
    this.cancelLessonCourse = document.getElementById('cancel-lesson-course');
    this.btnConfirmCancelLesson = document.getElementById('btn-confirm-cancel-lesson');
    this.btnAbortCancelLesson = document.getElementById('btn-abort-cancel-lesson');

    // --- PANTALLA 6: PERFIL DEL ESTUDIANTE (Jira M01-6, M01-7) ---
    this.viewProfile = document.getElementById('view-profile');
    this.btnBackProfile = document.getElementById('btn-back-profile');
    this.btnRefreshProfile = document.getElementById('btn-refresh-profile');
    this.profileAvatarLarge = document.getElementById('profile-avatar-large');
    this.profileHeroName = document.getElementById('profile-hero-name');
    this.profileHeroMatricula = document.getElementById('profile-hero-matricula');
    this.btnCopyMatricula = document.getElementById('btn-copy-matricula');
    this.profileHeroEmail = document.getElementById('profile-hero-email');

    // CEFR
    this.profileCefrCode = document.getElementById('profile-cefr-code');
    this.profileCefrName = document.getElementById('profile-cefr-name');
    this.profileCefrDesc = document.getElementById('profile-cefr-desc');
    this.profileCefrPercent = document.getElementById('profile-cefr-percent');
    this.profileCefrFill = document.getElementById('profile-cefr-fill');
    this.profileCefrNext = document.getElementById('profile-cefr-next');

    // Stats
    this.profileStatCompleted = document.getElementById('profile-stat-completed');
    this.profileStatHours = document.getElementById('profile-stat-hours');
    this.profileStatPending = document.getElementById('profile-stat-pending');

    // Formulario de Edición de Perfil
    this.formEditProfile = document.getElementById('form-edit-profile');
    this.profileInputName = document.getElementById('profile-input-name');
    this.profileInputPhone = document.getElementById('profile-input-phone');
    this.profileInputEmail = document.getElementById('profile-input-email');
    this.profileInputMatricula = document.getElementById('profile-input-matricula');
    this.btnSaveProfile = document.getElementById('btn-save-profile');

    // Logout desde Perfil
    this.btnLogoutFromProfile = document.getElementById('btn-logout-from-profile');
    this.profileUrl = 'api/profile.php';

    // --- PANTALLA 7: TICKET DIGITAL & COLISIÓN (Jira M01-17, M01-18) ---
    this.viewSuccessTicket = document.getElementById('view-success-ticket');
    this.btnBackSuccessTicket = document.getElementById('btn-back-success-ticket');
    this.ticketRefCode = document.getElementById('ticket-ref-code');
    this.ticketCourseLevel = document.getElementById('ticket-course-level');
    this.ticketProfAvatar = document.getElementById('ticket-prof-avatar');
    this.ticketProfName = document.getElementById('ticket-prof-name');
    this.ticketProfSpec = document.getElementById('ticket-prof-spec');
    this.ticketDateTime = document.getElementById('ticket-date-time');
    this.ticketStudentName = document.getElementById('ticket-student-name');
    this.ticketMeetUrl = document.getElementById('ticket-meet-url');
    this.btnCopyMeetLink = document.getElementById('btn-copy-meet-link');
    this.ticketBtnOpenMeet = document.getElementById('ticket-btn-open-meet');
    this.btnTicketToLessons = document.getElementById('btn-ticket-to-lessons');
    this.btnTicketToDash = document.getElementById('btn-ticket-to-dash');

    // Modal de Colisión Concurrente 409
    this.modalCollisionAlert = document.getElementById('modal-collision-alert');
    this.collisionSlotLabel = document.getElementById('collision-slot-label');
    this.collisionProfLabel = document.getElementById('collision-prof-label');
    this.btnCollisionRetry = document.getElementById('btn-collision-retry');
    this.btnDemoCollision = document.getElementById('btn-demo-collision');

    // URLs y Estados de Datos
    this.lessonsUrl = 'api/lessons.php';
    this.currentCancelTarget = null;
    this.lessonsState = { proximas: [], historial: [] };

    // Booking URL & Cascading State
    this.bookingUrl = 'api/booking.php';
    this.bookingState = {
      selectedCourse: null,
      selectedLevel: null,
      selectedProf: null,
      selectedDate: null,
      selectedSlot: null,
      cachedData: null,
      cachedDates: [],
      cachedSlots: []
    };
  }

  initEvents() {
    // QR Modal interactions
    if (this.btnShowQr && this.modalQr) {
      this.btnShowQr.addEventListener('click', () => this.modalQr.classList.add('active'));
      this.btnCloseQr.addEventListener('click', () => this.modalQr.classList.remove('active'));
      this.modalQr.addEventListener('click', (e) => {
        if (e.target === this.modalQr) this.modalQr.classList.remove('active');
      });
    }

    // Switch links (Iniciar Sesión <-> Crear Usuario)
    if (this.linkGoToRegister) {
      this.linkGoToRegister.addEventListener('click', (e) => {
        e.preventDefault();
        this.switchView('register');
      });
    }

    if (this.linkGoToLogin) {
      this.linkGoToLogin.addEventListener('click', (e) => {
        e.preventDefault();
        this.switchView('login');
      });
    }

    // Forms submissions
    if (this.formLogin) {
      this.formLogin.addEventListener('submit', (e) => this.handleLogin(e));
    }

    if (this.formRegister) {
      this.formRegister.addEventListener('submit', (e) => this.handleRegister(e));
    }

    if (this.formRecover) {
      this.formRecover.addEventListener('submit', (e) => this.handleRecover(e));
    }

    // Password view toggles
    if (this.toggleLoginPass && this.inputLoginPass) {
      this.toggleLoginPass.addEventListener('click', () => this.togglePasswordVisibility(this.inputLoginPass));
    }
    if (this.toggleRegPass && this.inputRegPass) {
      this.toggleRegPass.addEventListener('click', () => this.togglePasswordVisibility(this.inputRegPass));
    }

    // Modal interactions
    if (this.btnOpenRecover && this.modalRecover) {
      this.btnOpenRecover.addEventListener('click', (e) => {
        e.preventDefault();
        this.modalRecover.classList.add('active');
      });
    }
    if (this.btnCloseRecover && this.modalRecover) {
      this.btnCloseRecover.addEventListener('click', () => {
        this.modalRecover.classList.remove('active');
      });
    }
    if (this.modalRecover) {
      this.modalRecover.addEventListener('click', (e) => {
        if (e.target === this.modalRecover) this.modalRecover.classList.remove('active');
      });
    }

    // Logout
    if (this.btnLogout) {
      this.btnLogout.addEventListener('click', () => this.handleLogout());
    }

    // --- PANTALLA 3 EVENTOS DE NAVEGACIÓN Y CASCADA ---
    if (this.btnQuickSchedule) {
      this.btnQuickSchedule.addEventListener('click', () => this.navigateToSchedule());
    }

    if (this.btnBackSchedule) {
      this.btnBackSchedule.addEventListener('click', () => this.navigateToDashboard());
    }

    if (this.navDashboard) {
      this.navDashboard.addEventListener('click', () => this.navigateToDashboard());
    }

    if (this.navSchedule) {
      this.navSchedule.addEventListener('click', () => this.navigateToSchedule());
    }

    // --- PANTALLA 5 EVENTOS (MIS LECCIONES E HISTORIAL) ---
    if (this.navLessons) {
      this.navLessons.addEventListener('click', () => {
        this.navigateToLessons('upcoming');
      });
    }

    if (this.btnBackLessons) {
      this.btnBackLessons.addEventListener('click', () => {
        this.navigateToDashboard();
      });
    }

    if (this.tabLessonsUpcoming) {
      this.tabLessonsUpcoming.addEventListener('click', () => {
        this.switchLessonsTab('upcoming');
      });
    }

    if (this.tabLessonsHistory) {
      this.tabLessonsHistory.addEventListener('click', () => {
        this.switchLessonsTab('history');
      });
    }

    // Modal de Cancelación Eventos (M01-16)
    if (this.btnAbortCancelLesson && this.modalCancelLesson) {
      this.btnAbortCancelLesson.addEventListener('click', () => {
        this.modalCancelLesson.classList.remove('active');
        this.currentCancelTarget = null;
      });
      this.modalCancelLesson.addEventListener('click', (e) => {
        if (e.target === this.modalCancelLesson) {
          this.modalCancelLesson.classList.remove('active');
          this.currentCancelTarget = null;
        }
      });
    }

    if (this.btnConfirmCancelLesson) {
      this.btnConfirmCancelLesson.addEventListener('click', () => {
        this.executeLessonCancellation();
      });
    }

    // --- PANTALLA 6: EVENTOS DE PERFIL (Jira M01-6, M01-7) ---
    if (this.navProfile) {
      this.navProfile.addEventListener('click', () => {
        this.navigateToProfile();
      });
    }

    if (this.btnBackProfile) {
      this.btnBackProfile.addEventListener('click', () => {
        this.navigateToDashboard();
      });
    }

    if (this.btnRefreshProfile) {
      this.btnRefreshProfile.addEventListener('click', () => {
        this.loadStudentProfile(true);
      });
    }

    if (this.btnCopyMatricula) {
      this.btnCopyMatricula.addEventListener('click', () => {
        this.copyMatriculaToClipboard();
      });
    }

    if (this.formEditProfile) {
      this.formEditProfile.addEventListener('submit', (e) => {
        this.handleSaveProfile(e);
      });
    }

    if (this.btnLogoutFromProfile) {
      this.btnLogoutFromProfile.addEventListener('click', () => {
        this.handleLogout();
      });
    }

    const dashAvatar = document.getElementById('dash-avatar');
    if (dashAvatar) {
      dashAvatar.style.cursor = 'pointer';
      dashAvatar.title = 'Ver mi perfil';
      dashAvatar.addEventListener('click', () => this.navigateToProfile());
    }

    // --- PANTALLA 7: EVENTOS DE TICKET Y COLISIÓN 409 ---
    if (this.btnBackSuccessTicket) {
      this.btnBackSuccessTicket.addEventListener('click', () => this.navigateToDashboard());
    }

    if (this.btnTicketToLessons) {
      this.btnTicketToLessons.addEventListener('click', () => this.navigateToLessons('upcoming'));
    }

    if (this.btnTicketToDash) {
      this.btnTicketToDash.addEventListener('click', () => this.navigateToDashboard());
    }

    if (this.btnCopyMeetLink) {
      this.btnCopyMeetLink.addEventListener('click', () => this.copyMeetLinkToClipboard());
    }

    if (this.btnCollisionRetry) {
      this.btnCollisionRetry.addEventListener('click', () => this.dismissCollisionAndReload());
    }

    if (this.btnDemoCollision) {
      this.btnDemoCollision.addEventListener('click', () => this.handleConfirmBooking(true));
    }

    // Navegación por Chips de Pasos
    if (this.chipStep1) {
      this.chipStep1.addEventListener('click', () => {
        const sec = document.getElementById('section-step-1');
        if (sec) sec.scrollIntoView({ behavior: 'smooth' });
      });
    }

    if (this.chipStep2) {
      this.chipStep2.addEventListener('click', () => {
        if (!this.bookingState.selectedCourse) {
          this.showToast('Primero debes seleccionar un curso en el Paso 1', 'warning');
          return;
        }
        const sec = document.getElementById('section-step-2');
        if (sec) sec.scrollIntoView({ behavior: 'smooth' });
      });
    }

    if (this.chipStep3) {
      this.chipStep3.addEventListener('click', () => {
        if (!this.bookingState.selectedLevel) {
          this.showToast('Primero debes seleccionar un nivel en el Paso 2', 'warning');
          return;
        }
        const sec = document.getElementById('section-step-3');
        if (sec) sec.scrollIntoView({ behavior: 'smooth' });
      });
    }

    // Botón Continuar a Selección de Horario (Pantalla 4)
    if (this.btnProceedToCalendar) {
      this.btnProceedToCalendar.addEventListener('click', () => this.handleProceedToCalendar());
    }

    // --- PANTALLA 4 EVENTOS (CALENDARIO Y HORARIOS) ---
    if (this.btnBackCalendar) {
      this.btnBackCalendar.addEventListener('click', () => this.navigateToSchedule());
    }

    if (this.btnSubmitBooking) {
      this.btnSubmitBooking.addEventListener('click', () => this.handleConfirmBooking());
    }

    // Live preview para Correo Institucional generado automáticamente
    const regNameEl = document.getElementById('reg-name');
    const previewEmailEl = document.getElementById('preview-email');
    const updateEmailPreview = () => {
      if (!previewEmailEl) return;
      const name = regNameEl ? regNameEl.value.trim() : '';
      if (name) {
        const parts = name.toLowerCase().split(/\s+/).filter(Boolean);
        const auto = parts.length >= 2 ? (parts[0][0] + parts[parts.length - 1].replace(/[^a-z0-9]/g, '')) : parts[0].replace(/[^a-z0-9]/g, '');
        previewEmailEl.textContent = auto || 'tunombre';
      } else {
        previewEmailEl.textContent = 'tunombre';
      }
    };
    if (regNameEl) regNameEl.addEventListener('input', updateEmailPreview);

    // Listen to Singleton session events
    window.addEventListener('app:session-cleared', () => {
      this.showAuthViews();
    });

    // Quick Action "Agendar Nueva Lección" en Dashboard abre Pantalla 3
    if (this.btnQuickSchedule) {
      this.btnQuickSchedule.addEventListener('click', () => this.navigateToSchedule());
    }
  }

  switchView(view) {
    if (view === 'register') {
      this.viewLogin.classList.remove('active');
      this.viewRegister.classList.add('active');
      const regInput = document.getElementById('reg-name');
      if (regInput) regInput.focus();
    } else {
      this.viewRegister.classList.remove('active');
      this.viewLogin.classList.add('active');
      const loginInput = document.getElementById('login-email');
      if (loginInput) loginInput.focus();
    }
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  togglePasswordVisibility(input) {
    const isPass = input.getAttribute('type') === 'password';
    input.setAttribute('type', isPass ? 'text' : 'password');
  }

  async handleLogin(e) {
    if (e && e.preventDefault) e.preventDefault();
    const email = document.getElementById('login-email').value.trim();
    const password = document.getElementById('login-password').value.trim();
    const submitBtn = this.btnSubmitLogin || document.getElementById('btn-submit-login');

    if (!email || !password) {
      this.showToast('Por favor, ingresa tu email o usuario y contraseña', 'warning');
      return;
    }

    this.setButtonLoading(submitBtn, true, 'Iniciando sesión...');

    try {
      const response = await fetch(this.apiUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'login', identifier: email, password })
      });

      const data = await response.json();

      if (response.ok && data.success) {
        // Guardar estado en el SINGLETON
        this.sessionManager.setSession(data.student, data.token);
        this.showToast(data.message, 'success');
        this.renderDashboard(data.student);
      } else {
        this.showToast(data.message || 'Error al iniciar sesión', 'error');
      }
    } catch (err) {
      console.warn("Fallo de red, utilizando modo resiliencia (Reglas de Larsson):", err);
      // Fallback offline resiliente
      if (email.length >= 3 && password.length >= 4) {
        const fallbackStudent = {
          id: 'STU-OFFLINE',
          nombre: email.includes('@') ? email.split('@')[0] : email,
          email: email.includes('@') ? email : `${email}@english.edu`,
          nivel_actual: 'A1'
        };
        this.sessionManager.setSession(fallbackStudent, 'offline-token-xyz');
        this.showToast('Sesión iniciada (Modo offline resiliente)', 'info');
        this.renderDashboard(fallbackStudent);
      } else {
        this.showToast('No se pudo conectar con el servidor', 'error');
      }
    } finally {
      this.setButtonLoading(submitBtn, false, 'Iniciar Sesión');
    }
  }

  async handleRegister(e) {
    if (e && e.preventDefault) e.preventDefault();
    
    const nombreEl = document.getElementById('reg-name');
    const passwordEl = document.getElementById('reg-password');
    const phoneEl = document.getElementById('reg-phone');
    const submitBtn = this.btnSubmitReg || document.getElementById('btn-submit-reg');

    const nombre = nombreEl ? nombreEl.value.trim() : '';
    const password = passwordEl ? passwordEl.value.trim() : '';
    const telefono = phoneEl ? phoneEl.value.trim() : '';

    if (!nombre) {
      this.showToast('Por favor, ingresa tu nombre completo', 'warning');
      if (nombreEl) nombreEl.focus();
      return;
    }

    if (!password || password.length < 6) {
      this.showToast('La contraseña debe tener un mínimo de 6 caracteres', 'warning');
      if (passwordEl) passwordEl.focus();
      return;
    }

    this.setButtonLoading(submitBtn, true, 'Generando matrícula y cuenta...');

    try {
      const response = await fetch(this.apiUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
          action: 'register', 
          nombre, 
          password, 
          telefono 
        })
      });

      const data = await response.json();

      if (response.ok && data.success) {
        // Guardar sesión en el SINGLETON
        this.sessionManager.setSession(data.student, data.token);
        this.showToast(`¡Matrícula Asignada: ${data.matricula || data.student.matricula}!`, 'success');
        this.renderDashboard(data.student);
      } else {
        this.showToast(data.message || 'Error al registrar usuario', 'error');
      }
    } catch (err) {
      console.error("Error al registrar:", err);
      this.showToast('Error de comunicación con el servidor', 'error');
    } finally {
      this.setButtonLoading(submitBtn, false, 'Crear Cuenta & Asignar Matrícula');
    }
  }

  async handleRecover(e) {
    if (e && e.preventDefault) e.preventDefault();
    const email = document.getElementById('recover-email').value.trim();
    const submitBtn = document.getElementById('btn-submit-recover');

    if (!email) {
      this.showToast('Ingresa tu usuario o correo', 'warning');
      return;
    }

    this.setButtonLoading(submitBtn, true, 'Enviando...');

    try {
      const res = await fetch(this.apiUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'recover', identifier: email })
      });
      const data = await res.json();
      this.showToast(data.message, 'success');
      this.modalRecover.classList.remove('active');
    } catch (err) {
      this.showToast('Instrucciones enviadas al correo', 'info');
      this.modalRecover.classList.remove('active');
    } finally {
      this.setButtonLoading(submitBtn, false, 'Enviar Instrucciones');
    }
  }

  handleLogout() {
    this.sessionManager.clearSession();
    this.showToast('Sesión cerrada correctamente', 'info');
  }

  checkInitialSession() {
    if (this.sessionManager.isAuthenticated()) {
      const student = this.sessionManager.getStudent();
      this.renderDashboard(student);
    }
  }

  async renderDashboard(student) {
    if (!student) return;
    this.authContainer.style.display = 'none';
    if (this.viewSchedule) this.viewSchedule.style.display = 'none';
    if (this.viewCalendar) this.viewCalendar.style.display = 'none';
    if (this.viewLessons) this.viewLessons.style.display = 'none';
    if (this.viewProfile) this.viewProfile.style.display = 'none';
    if (this.viewSuccessTicket) this.viewSuccessTicket.style.display = 'none';
    this.viewDashboard.classList.add('active');
    if (this.appBottomNav) this.appBottomNav.style.display = 'flex';
    this.updateActiveNav('nav-dashboard');

    const firstNameEl = document.getElementById('dash-student-firstname');
    const nameEl = document.getElementById('dash-student-name');
    const emailEl = document.getElementById('dash-student-email');
    const matriculaEl = document.getElementById('dash-student-matricula');
    const levelEl = document.getElementById('dash-student-level');
    const avatarEl = document.getElementById('dash-avatar');

    const fullName = student.nombre || student.username || 'Estudiante';
    const firstName = fullName.split(' ')[0];
    if (firstNameEl) firstNameEl.textContent = firstName;
    if (nameEl) nameEl.textContent = fullName;
    if (emailEl) emailEl.textContent = student.email || `${student.username}@englishmade.edu`;
    if (matriculaEl) matriculaEl.textContent = `Matrícula: ${student.matricula || student.username || 'EMD-2026-0001'}`;
    
    const nivel = student.nivel_actual || student.nivel || 'A1';
    const nivelNombres = { 'A1': 'Principiante', 'A2': 'Básico', 'B1': 'Intermedio', 'B2': 'Avanzado', 'C1': 'Fluido' };
    const desc = nivelNombres[nivel] || 'Principiante';
    if (levelEl) levelEl.textContent = `Nivel: ${nivel} (${desc})`;
    
    const initials = fullName
      .split(' ')
      .map(n => n[0])
      .join('')
      .substring(0, 2)
      .toUpperCase();
    if (avatarEl) avatarEl.textContent = initials;

    // Cargar datos dinámicos desde api/dashboard.php
    try {
      const studentId = student.id || '';
      const matricula = student.matricula || student.username || '';
      const res = await fetch(`${this.dashboardUrl}?action=summary&student_id=${studentId}&matricula=${matricula}`);
      const data = await res.json();

      if (res.ok && data.success) {
        // 1. Renderizar Estadísticas (M01-10)
        if (data.stats) {
          const sc = document.getElementById('stat-completed');
          const sh = document.getElementById('stat-hours');
          const sp = document.getElementById('stat-pending');
          if (sc) sc.textContent = data.stats.lecciones_completadas;
          if (sh) sh.textContent = `${data.stats.horas_acumuladas}h`;
          if (sp) sp.textContent = data.stats.lecciones_pendientes;
        }

        // 2. Renderizar Próxima Lección (M01-9)
        const nextContainer = document.getElementById('dash-next-lesson-container');
        if (nextContainer) {
          if (data.next_lesson) {
            const nl = data.next_lesson;
            nextContainer.innerHTML = `
              <div class="lesson-card-featured">
                <div class="lesson-top-meta">
                  <span class="lesson-course-badge">${nl.curso}</span>
                  <span class="lesson-time-pill">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>${nl.fecha} • ${nl.hora_inicio} - ${nl.hora_fin}</span>
                  </span>
                </div>
                <h3 class="lesson-title">Nivel: ${nl.nivel}</h3>
                <div class="lesson-teacher-row">
                  <div class="teacher-avatar-sm">${nl.profesor.split(' ').map(n=>n[0]).join('').substring(0,2)}</div>
                  <div class="teacher-details">
                    <div class="teacher-name">${nl.profesor}</div>
                    <div class="teacher-specialty">${nl.especialidad} • ⭐ ${nl.rating}</div>
                  </div>
                </div>
                <a href="${nl.enlace_sesion}" target="_blank" rel="noopener noreferrer" class="btn-join-live">
                  <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                  </svg>
                  <span>Entrar a Clase en Vivo</span>
                </a>
              </div>
            `;
          } else {
            nextContainer.innerHTML = `
              <div class="empty-lesson-card">
                <div class="empty-icon">
                  <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
                <h4>¡Sin clases programadas!</h4>
                <p>Elige tu profesor y reserva tu próxima lección de conversación en vivo.</p>
                <button type="button" class="btn-join-live" id="btn-empty-schedule" style="display:inline-flex; width:auto; padding:10px 20px; margin:0 auto; font-size:0.82rem;">
                  <span>Agendar Primera Lección</span>
                </button>
              </div>
            `;
            const emptyBtn = document.getElementById('btn-empty-schedule');
            if (emptyBtn) {
              emptyBtn.addEventListener('click', () => {
                this.navigateToSchedule();
              });
            }
          }
        }

        // 3. Renderizar Catálogo de Cursos de la Academia
        const coursesList = document.getElementById('dash-courses-list');
        if (coursesList && data.cursos && data.cursos.length > 0) {
          coursesList.innerHTML = data.cursos.map(c => `
            <div class="course-card-mini" data-id="${c.id}" title="${c.nombre}">
              <div class="course-mini-top">
                <div class="course-mini-icon">
                  ${this.getCourseIconSvg(c.icono)}
                </div>
                <span class="course-mini-tag">${c.codigo}</span>
              </div>
              <div class="course-mini-title">${c.nombre}</div>
              <div class="course-mini-desc">${c.descripcion}</div>
            </div>
          `).join('');

          // Click en curso rápido del dashboard abre directamente la Pantalla 3 con ese curso seleccionado
          coursesList.querySelectorAll('.course-card-mini').forEach(card => {
            card.addEventListener('click', () => {
              const cid = parseInt(card.getAttribute('data-id'), 10);
              this.navigateToSchedule(cid);
            });
          });
        }
      }
    } catch (e) {
      console.warn("No se pudo cargar el resumen del dashboard (Modo offline):", e);
    }
  }

  // =========================================================================
  // PANTALLA 3: MÉTODOS DE FILTRO EN CASCADA Y NAVEGACIÓN (M01-11, M01-12)
  // =========================================================================

  updateActiveNav(activeId) {
    [this.navDashboard, this.navSchedule, this.navLessons, this.navProfile].forEach(item => {
      if (item) item.classList.remove('active');
    });
    const current = document.getElementById(activeId);
    if (current) current.classList.add('active');
  }

  async navigateToSchedule(preselectedCourseId = null) {
    if (this.viewDashboard) this.viewDashboard.classList.remove('active');
    if (this.viewCalendar) this.viewCalendar.style.display = 'none';
    if (this.viewLessons) this.viewLessons.style.display = 'none';
    if (this.viewProfile) this.viewProfile.style.display = 'none';
    if (this.viewSuccessTicket) this.viewSuccessTicket.style.display = 'none';
    if (this.viewSchedule) this.viewSchedule.style.display = 'block';
    if (this.appBottomNav) this.appBottomNav.style.display = 'flex';
    this.updateActiveNav('nav-schedule');
    window.scrollTo({ top: 0, behavior: 'smooth' });

    await this.loadBookingData(preselectedCourseId);
  }

  navigateToDashboard() {
    if (this.viewSchedule) this.viewSchedule.style.display = 'none';
    if (this.viewCalendar) this.viewCalendar.style.display = 'none';
    if (this.viewLessons) this.viewLessons.style.display = 'none';
    if (this.viewProfile) this.viewProfile.style.display = 'none';
    if (this.viewSuccessTicket) this.viewSuccessTicket.style.display = 'none';
    if (this.viewDashboard) this.viewDashboard.classList.add('active');
    if (this.appBottomNav) this.appBottomNav.style.display = 'flex';
    this.updateActiveNav('nav-dashboard');
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  async loadBookingData(preselectedCourseId = null) {
    if (!this.bookingState.cachedData) {
      if (this.scheduleCoursesContainer) {
        this.scheduleCoursesContainer.innerHTML = `
          <div style="padding: 20px; text-align: center; color: #0284c7; font-size: 0.84rem;">
            <span class="spinner" style="border-top-color: #0284c7; display:inline-block; margin-bottom:8px;"></span><br>
            Cargando programas y profesores certificados...
          </div>
        `;
      }

      try {
        const res = await fetch(this.bookingUrl);
        const json = await res.json();
        if (res.ok && json.success) {
          this.bookingState.cachedData = json.data;
        } else {
          this.showToast('Error al cargar catálogo de agendamiento', 'error');
          return;
        }
      } catch (err) {
        console.warn("Fallo de red al cargar catálogo de agendamiento:", err);
        this.showToast('Error de conexión al cargar cursos', 'error');
        return;
      }
    }

    this.renderScheduleCourses(preselectedCourseId);
  }

  renderScheduleCourses(preselectedCourseId = null) {
    const data = this.bookingState.cachedData;
    if (!data || !this.scheduleCoursesContainer) return;

    this.scheduleCoursesContainer.innerHTML = data.cursos.map(c => `
      <div class="course-select-card" data-course-id="${c.id}" id="course-opt-${c.id}">
        <div class="course-select-icon">
          ${this.getCourseIconSvg(c.icono)}
        </div>
        <div class="course-select-info">
          <div class="course-select-tag">${c.codigo}</div>
          <div class="course-select-title">${c.nombre}</div>
          <div class="course-select-desc">${c.descripcion}</div>
        </div>
        <div class="radio-check-indicator"></div>
      </div>
    `).join('');

    // Listener para selección de curso
    this.scheduleCoursesContainer.querySelectorAll('.course-select-card').forEach(card => {
      card.addEventListener('click', () => {
        const cid = parseInt(card.getAttribute('data-course-id'), 10);
        this.selectCourse(cid);
      });
    });

    if (preselectedCourseId) {
      this.selectCourse(preselectedCourseId);
    }
  }

  selectCourse(courseId) {
    const data = this.bookingState.cachedData;
    if (!data) return;

    const course = data.cursos.find(c => c.id === courseId);
    if (!course) return;

    this.bookingState.selectedCourse = course;
    this.bookingState.selectedLevel = null;
    this.bookingState.selectedProf = null;

    // 1. Resaltar tarjeta seleccionada en Paso 1
    this.scheduleCoursesContainer.querySelectorAll('.course-select-card').forEach(card => {
      card.classList.remove('selected');
    });
    const selectedEl = document.getElementById(`course-opt-${courseId}`);
    if (selectedEl) selectedEl.classList.add('selected');

    // 2. Actualizar Badges y Breadcrumbs
    if (this.badgeStep1) {
      this.badgeStep1.textContent = 'Completado ✓';
      this.badgeStep1.className = 'badge-step-status status-ready';
    }
    if (this.badgeStep2) {
      this.badgeStep2.textContent = 'Paso Actual';
      this.badgeStep2.className = 'badge-step-status status-ready';
    }
    if (this.badgeStep3) {
      this.badgeStep3.textContent = 'Esperando Nivel';
      this.badgeStep3.className = 'badge-step-status status-waiting';
    }

    if (this.chipStep1) {
      this.chipStep1.className = 'step-chip completed';
    }
    if (this.chipStep2) {
      this.chipStep2.className = 'step-chip active';
    }
    if (this.chipStep3) {
      this.chipStep3.className = 'step-chip';
    }

    // 3. Desbloquear y Renderizar Paso 2 (Niveles en Cascada)
    if (this.levelsLockedPlaceholder) this.levelsLockedPlaceholder.style.display = 'none';
    if (this.scheduleLevelsContainer) {
      this.scheduleLevelsContainer.style.display = 'grid';

      const filteredLevels = data.niveles.filter(n => n.curso_id === courseId);
      this.scheduleLevelsContainer.innerHTML = filteredLevels.map(lvl => `
        <div class="level-chip-card" data-level-id="${lvl.id}" id="level-opt-${lvl.id}">
          <div class="level-chip-top">
            <span class="level-code-badge">${lvl.codigo}</span>
            <div class="radio-check-indicator"></div>
          </div>
          <div>
            <div class="level-name">${lvl.nombre}</div>
            <div class="level-desc">${lvl.descripcion}</div>
          </div>
        </div>
      `).join('');

      this.scheduleLevelsContainer.querySelectorAll('.level-chip-card').forEach(card => {
        card.addEventListener('click', () => {
          const lid = parseInt(card.getAttribute('data-level-id'), 10);
          this.selectLevel(lid);
        });
      });
    }

    // 4. Resetear y Bloquear Paso 3
    if (this.profsLockedPlaceholder) this.profsLockedPlaceholder.style.display = 'block';
    if (this.scheduleProfsContainer) {
      this.scheduleProfsContainer.style.display = 'none';
      this.scheduleProfsContainer.innerHTML = '';
    }
    if (this.scheduleSummaryBar) this.scheduleSummaryBar.style.display = 'none';

    // Desplazar suavemente hacia el Paso 2
    const sec2 = document.getElementById('section-step-2');
    if (sec2) sec2.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  selectLevel(levelId) {
    const data = this.bookingState.cachedData;
    if (!data || !this.bookingState.selectedCourse) return;

    const level = data.niveles.find(n => n.id === levelId);
    if (!level) return;

    this.bookingState.selectedLevel = level;
    this.bookingState.selectedProf = null;

    // 1. Resaltar tarjeta seleccionada en Paso 2
    this.scheduleLevelsContainer.querySelectorAll('.level-chip-card').forEach(card => {
      card.classList.remove('selected');
    });
    const selectedEl = document.getElementById(`level-opt-${levelId}`);
    if (selectedEl) selectedEl.classList.add('selected');

    // 2. Actualizar Badges y Breadcrumbs
    if (this.badgeStep2) {
      this.badgeStep2.textContent = 'Completado ✓';
      this.badgeStep2.className = 'badge-step-status status-ready';
    }
    if (this.badgeStep3) {
      this.badgeStep3.textContent = 'Paso Actual';
      this.badgeStep3.className = 'badge-step-status status-ready';
    }

    if (this.chipStep2) {
      this.chipStep2.className = 'step-chip completed';
    }
    if (this.chipStep3) {
      this.chipStep3.className = 'step-chip active';
    }

    // 3. Desbloquear y Renderizar Paso 3 (Profesores en Cascada - M01-12)
    if (this.profsLockedPlaceholder) this.profsLockedPlaceholder.style.display = 'none';
    if (this.scheduleProfsContainer) {
      this.scheduleProfsContainer.style.display = 'flex';

      // Filtrar profesores que imparten este curso
      const courseId = this.bookingState.selectedCourse.id;
      let filteredProfs = data.profesores.filter(p => p.cursos_asociados && p.cursos_asociados.includes(courseId));
      if (filteredProfs.length === 0) {
        filteredProfs = data.profesores; // Fallback resiliente
      }

      this.scheduleProfsContainer.innerHTML = filteredProfs.map(prof => {
        const initials = prof.nombre
          .split(' ')
          .filter(Boolean)
          .slice(-2)
          .map(w => w[0])
          .join('')
          .toUpperCase() || 'PR';

        return `
          <div class="prof-select-card" data-prof-id="${prof.id}" id="prof-opt-${prof.id}">
            <div class="prof-card-main-row">
              <div class="prof-avatar-circle">${initials}</div>
              <div class="prof-card-info">
                <div class="prof-name-row">
                  <span class="prof-name">${prof.nombre}</span>
                  <span class="prof-rating-badge">★ ${prof.rating.toFixed(2)}</span>
                </div>
                <div class="prof-specialty">${prof.especialidad}</div>
              </div>
              <div class="radio-check-indicator"></div>
            </div>
            <div class="prof-bio">
              "${prof.biografia}"
            </div>
          </div>
        `;
      }).join('');

      this.scheduleProfsContainer.querySelectorAll('.prof-select-card').forEach(card => {
        card.addEventListener('click', () => {
          const pid = parseInt(card.getAttribute('data-prof-id'), 10);
          this.selectProfessor(pid);
        });
      });
    }

    if (this.scheduleSummaryBar) this.scheduleSummaryBar.style.display = 'none';

    // Desplazar suavemente hacia el Paso 3
    const sec3 = document.getElementById('section-step-3');
    if (sec3) sec3.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  selectProfessor(profId) {
    const data = this.bookingState.cachedData;
    if (!data || !this.bookingState.selectedCourse || !this.bookingState.selectedLevel) return;

    const prof = data.profesores.find(p => p.id === profId);
    if (!prof) return;

    this.bookingState.selectedProf = prof;

    // 1. Resaltar tarjeta seleccionada en Paso 3
    this.scheduleProfsContainer.querySelectorAll('.prof-select-card').forEach(card => {
      card.classList.remove('selected');
    });
    const selectedEl = document.getElementById(`prof-opt-${profId}`);
    if (selectedEl) selectedEl.classList.add('selected');

    // 2. Actualizar Badges y Breadcrumbs
    if (this.badgeStep3) {
      this.badgeStep3.textContent = 'Completado ✓';
      this.badgeStep3.className = 'badge-step-status status-ready';
    }
    if (this.chipStep3) {
      this.chipStep3.className = 'step-chip completed';
    }

    // 3. Actualizar Resumen Flotante (M01-12)
    if (this.summaryCourseName) this.summaryCourseName.textContent = this.bookingState.selectedCourse.nombre;
    if (this.summaryLevelName) this.summaryLevelName.textContent = `${this.bookingState.selectedLevel.codigo} - ${this.bookingState.selectedLevel.nombre}`;
    if (this.summaryProfName) this.summaryProfName.textContent = `${prof.nombre} (★ ${prof.rating.toFixed(2)})`;

    if (this.scheduleSummaryBar) {
      this.scheduleSummaryBar.style.display = 'block';
      this.scheduleSummaryBar.scrollIntoView({ behavior: 'smooth', block: 'end' });
    }
  }

  async handleProceedToCalendar() {
    const { selectedCourse, selectedLevel, selectedProf } = this.bookingState;
    if (!selectedCourse || !selectedLevel || !selectedProf) {
      this.showToast('Por favor completa los 3 pasos de selección antes de continuar', 'warning');
      return;
    }

    await this.navigateToCalendar();
  }

  // =========================================================================
  // PANTALLA 4: MÉTODOS DE CALENDARIO Y SLOTS (M01-13, M01-14)
  // =========================================================================

  async navigateToCalendar() {
    const { selectedCourse, selectedLevel, selectedProf } = this.bookingState;
    if (!selectedCourse || !selectedLevel || !selectedProf) {
      this.showToast('Por favor completa los pasos de selección antes de continuar', 'warning');
      return;
    }

    if (this.viewDashboard) this.viewDashboard.classList.remove('active');
    if (this.viewSchedule) this.viewSchedule.style.display = 'none';
    if (this.viewLessons) this.viewLessons.style.display = 'none';
    if (this.viewProfile) this.viewProfile.style.display = 'none';
    if (this.viewSuccessTicket) this.viewSuccessTicket.style.display = 'none';
    if (this.viewCalendar) this.viewCalendar.style.display = 'block';
    if (this.appBottomNav) this.appBottomNav.style.display = 'flex';
    this.updateActiveNav('nav-schedule');
    window.scrollTo({ top: 0, behavior: 'smooth' });

    // Actualizar Banner del Profesor Seleccionado
    const initials = selectedProf.nombre
      .split(' ')
      .filter(Boolean)
      .slice(-2)
      .map(w => w[0])
      .join('')
      .toUpperCase() || 'PR';

    if (this.calProfAvatar) this.calProfAvatar.textContent = initials;
    if (this.calProfName) this.calProfName.textContent = selectedProf.nombre;
    if (this.calCourseLevel) this.calCourseLevel.textContent = `${selectedCourse.nombre} • ${selectedLevel.codigo} ${selectedLevel.nombre}`;
    if (this.calProfRating) this.calProfRating.textContent = `★ ${selectedProf.rating.toFixed(2)}`;

    // Resetear selección de slot y ocultar barra de confirmación
    this.bookingState.selectedSlot = null;
    if (this.calendarConfirmBar) this.calendarConfirmBar.style.display = 'none';

    // Cargar fechas disponibles para este profesor
    await this.loadCalendarDates();
  }

  async loadCalendarDates() {
    const profId = this.bookingState.selectedProf ? this.bookingState.selectedProf.id : 1;
    if (this.calendarDatesStrip) {
      this.calendarDatesStrip.innerHTML = `
        <div style="padding: 14px; text-align: center; color: #0284c7; font-size: 0.8rem; width: 100%;">
          <span class="spinner" style="border-top-color: #0284c7; display:inline-block; margin-bottom:4px;"></span><br>
          Consultando fechas y disponibilidad...
        </div>
      `;
    }

    try {
      const res = await fetch(`${this.bookingUrl}?action=available_dates&profesor_id=${profId}`);
      const data = await res.json();
      if (res.ok && data.success && data.dates.length > 0) {
        this.bookingState.cachedDates = data.dates;
        this.renderCalendarDates(data.dates);
      } else {
        if (this.calendarDatesStrip) {
          this.calendarDatesStrip.innerHTML = `<div style="font-size:0.75rem; color:#64748b; padding:10px;">No hay fechas disponibles próximas para este docente.</div>`;
        }
      }
    } catch (err) {
      console.warn("Error cargando fechas de calendario:", err);
      this.showToast('Error de conexión al cargar fechas del calendario', 'error');
    }
  }

  renderCalendarDates(dates) {
    if (!this.calendarDatesStrip || !dates) return;

    this.calendarDatesStrip.innerHTML = dates.map((d, idx) => `
      <div class="date-chip-card ${idx === 0 ? 'active' : ''}" data-fecha="${d.fecha}" id="date-chip-${d.fecha}">
        <span class="date-chip-day">${d.dia_semana}</span>
        <span class="date-chip-num">${d.dia_num}</span>
        <span class="date-chip-month">${d.mes}</span>
        <span class="date-chip-slots-pill">${d.slots_disponibles} libres</span>
      </div>
    `).join('');

    // Listener para cambiar fecha
    this.calendarDatesStrip.querySelectorAll('.date-chip-card').forEach(card => {
      card.addEventListener('click', () => {
        const fecha = card.getAttribute('data-fecha');
        this.selectDate(fecha);
      });
    });

    // Seleccionar automáticamente la primera fecha disponible
    if (dates.length > 0) {
      this.selectDate(dates[0].fecha);
    }
  }

  selectDate(fechaStr) {
    this.bookingState.selectedDate = fechaStr;
    this.bookingState.selectedSlot = null;

    // Resaltar chip activo
    if (this.calendarDatesStrip) {
      this.calendarDatesStrip.querySelectorAll('.date-chip-card').forEach(c => c.classList.remove('active'));
      const activeEl = document.getElementById(`date-chip-${fechaStr}`);
      if (activeEl) {
        activeEl.classList.add('active');
        activeEl.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
      }
    }

    // Actualizar etiqueta del día
    const dateObj = this.bookingState.cachedDates ? this.bookingState.cachedDates.find(d => d.fecha === fechaStr) : null;
    if (this.calSelectedDayLabel) {
      if (dateObj) {
        this.calSelectedDayLabel.textContent = `${dateObj.dia_semana} ${dateObj.dia_num} de ${dateObj.mes}`;
      } else {
        this.calSelectedDayLabel.textContent = fechaStr;
      }
    }

    if (this.calendarConfirmBar) this.calendarConfirmBar.style.display = 'none';

    this.loadSlotsForDate(fechaStr);
  }

  async loadSlotsForDate(fechaStr) {
    const profId = this.bookingState.selectedProf ? this.bookingState.selectedProf.id : 1;

    // Mostrar loaders en turnos
    [this.slotsGridMorning, this.slotsGridAfternoon, this.slotsGridEvening].forEach(grid => {
      if (grid) grid.innerHTML = `<div class="empty-shift-msg"><span class="spinner" style="border-top-color:#0284c7;display:inline-block;width:16px;height:16px;margin-bottom:4px;"></span> Cargando horarios...</div>`;
    });

    try {
      const res = await fetch(`${this.bookingUrl}?action=slots&profesor_id=${profId}&fecha=${fechaStr}`);
      const data = await res.json();

      if (res.ok && data.success) {
        this.bookingState.cachedSlots = data.slots;
        this.renderSlotsGrids(data.slots);
      } else {
        this.showToast('No se pudieron obtener los horarios para este día', 'error');
      }
    } catch (err) {
      console.warn("Error cargando slots:", err);
      this.showToast('Error de conexión al cargar horarios', 'error');
    }
  }

  renderSlotsGrids(slots) {
    const morningSlots = slots.filter(s => s.turno === 'morning');
    const afternoonSlots = slots.filter(s => s.turno === 'afternoon');
    const eveningSlots = slots.filter(s => s.turno === 'evening');

    const availableCount = slots.filter(s => s.disponible).length;
    if (this.calAvailableCount) {
      this.calAvailableCount.textContent = `${availableCount} Horarios Libres`;
    }

    const renderShift = (container, shiftSlots) => {
      if (!container) return;
      if (shiftSlots.length === 0) {
        container.innerHTML = `<div class="empty-shift-msg">Sin horarios programados en este turno.</div>`;
        return;
      }

      container.innerHTML = shiftSlots.map(s => {
        const isFree = s.disponible;
        const stateClass = isFree ? 'state-available' : 'state-occupied';
        const badgeText = isFree ? '🟢 Libre' : '⚪ Ocupado';

        return `
          <div class="slot-card ${stateClass}" data-slot-id="${s.id}" id="slot-card-${s.id}">
            <span class="slot-time-text">${s.hora_rango}</span>
            <span class="slot-badge">${badgeText}</span>
          </div>
        `;
      }).join('');

      container.querySelectorAll('.slot-card.state-available').forEach(card => {
        card.addEventListener('click', () => {
          const sid = parseInt(card.getAttribute('data-slot-id'), 10);
          const slot = slots.find(item => item.id === sid);
          if (slot) this.selectSlot(slot);
        });
      });

      container.querySelectorAll('.slot-card.state-occupied').forEach(card => {
        card.addEventListener('click', () => {
          this.showToast('Este horario ya está reservado por otro estudiante. Por favor elige uno verde (disponible).', 'warning');
        });
      });
    };

    renderShift(this.slotsGridMorning, morningSlots);
    renderShift(this.slotsGridAfternoon, afternoonSlots);
    renderShift(this.slotsGridEvening, eveningSlots);
  }

  selectSlot(slot) {
    this.bookingState.selectedSlot = slot;

    // Resaltar slot en Azul (🔵 M01-14)
    document.querySelectorAll('.slot-card').forEach(c => {
      if (c.classList.contains('state-available')) {
        c.classList.remove('state-selected');
        const badge = c.querySelector('.slot-badge');
        if (badge) badge.textContent = '🟢 Libre';
      }
    });

    const activeSlotCard = document.getElementById(`slot-card-${slot.id}`);
    if (activeSlotCard) {
      activeSlotCard.classList.add('state-selected');
      const badge = activeSlotCard.querySelector('.slot-badge');
      if (badge) badge.textContent = '✓ Elegido';
    }

    // Actualizar Resumen de Confirmación
    const { selectedCourse, selectedLevel, selectedProf, selectedDate } = this.bookingState;
    if (this.calConfirmCourse) this.calConfirmCourse.textContent = `${selectedCourse.nombre} (${selectedLevel.codigo})`;
    if (this.calConfirmProf) this.calConfirmProf.textContent = `${selectedProf.nombre}`;
    if (this.calConfirmTime) this.calConfirmTime.textContent = `${selectedDate} a las ${slot.hora_rango}`;

    if (this.calendarConfirmBar) {
      this.calendarConfirmBar.style.display = 'block';
      this.calendarConfirmBar.scrollIntoView({ behavior: 'smooth', block: 'end' });
    }
  }

  async handleConfirmBooking(simulateCollision = false) {
    const student = this.sessionManager.getStudent();
    const { selectedCourse, selectedLevel, selectedProf, selectedSlot, selectedDate } = this.bookingState;

    if (!student || !selectedSlot || !selectedCourse || !selectedLevel || !selectedProf) {
      this.showToast('Información de reserva incompleta', 'warning');
      return;
    }

    const btn = simulateCollision ? this.btnDemoCollision : this.btnSubmitBooking;
    this.setButtonLoading(btn, true, simulateCollision ? 'Simulando colisión...' : 'Confirmando en tiempo real...');

    try {
      const response = await fetch(this.bookingUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'reserve',
          student_id: student.id || 1,
          horario_id: selectedSlot.id,
          curso_id: selectedCourse.id,
          nivel_id: selectedLevel.id,
          profesor_id: selectedProf.id,
          simulate_collision: simulateCollision
        })
      });

      const data = await response.json();

      if (response.status === 201 && data.success) {
        // PANTALLA 7: FLUJO EXITOSO - VOUCHER / TICKET DIGITAL (M01-17)
        this.renderSuccessTicket(data.lesson, selectedCourse, selectedLevel, selectedProf, selectedSlot, selectedDate, student);
        
        // Limpiar completamente el flujo de selección de Pantalla 4 y 3
        this.resetBookingFlow();

        // Recargar Dashboard en segundo plano
        this.renderDashboard(student);
      } else if (response.status === 409 || data.collision) {
        // PANTALLA 7: FLUJO DE CONFLICTO CONCURRENTE HTTP 409 (M01-18)
        this.showCollisionModal(selectedProf, selectedSlot, selectedDate);
      } else {
        this.showToast(data.message || 'Error al agendar la lección', 'error');
      }
    } catch (err) {
      console.error("Error al agendar:", err);
      this.showToast('Error de conexión con el servidor de reservas', 'error');
    } finally {
      this.setButtonLoading(this.btnSubmitBooking, false, 'Confirmar Reserva de Lección');
      if (this.btnDemoCollision) {
        this.setButtonLoading(this.btnDemoCollision, false, '⚡ Simular Colisión Concurrente (Demo 409)');
      }
    }
  }

  getCourseIconSvg(name) {
    if (name === 'briefcase') {
      return '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>';
    } else if (name === 'award') {
      return '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>';
    } else if (name === 'message-circle') {
      return '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>';
    }
    return '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>';
  }

  showAuthViews() {
    if (this.viewDashboard) this.viewDashboard.classList.remove('active');
    if (this.viewSchedule) this.viewSchedule.style.display = 'none';
    if (this.viewCalendar) this.viewCalendar.style.display = 'none';
    if (this.viewLessons) this.viewLessons.style.display = 'none';
    if (this.viewProfile) this.viewProfile.style.display = 'none';
    if (this.viewSuccessTicket) this.viewSuccessTicket.style.display = 'none';
    if (this.appBottomNav) this.appBottomNav.style.display = 'none';
    this.authContainer.style.display = 'block';
    this.switchView('login');
  }

  // =========================================================================
  // LIMPIEZA DEL FLUJO DE AGENDAMIENTO TRAS CONFIRMACIÓN (PANTALLA 4 Y 3)
  // =========================================================================
  resetBookingFlow() {
    this.bookingState.selectedCourse = null;
    this.bookingState.selectedLevel = null;
    this.bookingState.selectedProf = null;
    this.bookingState.selectedDate = null;
    this.bookingState.selectedSlot = null;

    // Resetear tarjetas de cursos en Pantalla 3
    if (this.scheduleCoursesContainer) {
      this.scheduleCoursesContainer.querySelectorAll('.course-select-card').forEach(c => c.classList.remove('selected'));
    }

    // Resetear badges de pasos
    if (this.badgeStep1) {
      this.badgeStep1.textContent = 'Paso Actual';
      this.badgeStep1.className = 'badge-step-status status-ready';
    }
    if (this.badgeStep2) {
      this.badgeStep2.textContent = 'Esperando Curso';
      this.badgeStep2.className = 'badge-step-status status-waiting';
    }
    if (this.badgeStep3) {
      this.badgeStep3.textContent = 'Esperando Nivel';
      this.badgeStep3.className = 'badge-step-status status-waiting';
    }

    // Ocultar niveles y profesores bloqueados
    if (this.levelsLockedPlaceholder) this.levelsLockedPlaceholder.style.display = 'block';
    if (this.scheduleLevelsContainer) {
      this.scheduleLevelsContainer.style.display = 'none';
      this.scheduleLevelsContainer.innerHTML = '';
    }

    if (this.profsLockedPlaceholder) this.profsLockedPlaceholder.style.display = 'block';
    if (this.scheduleProfsContainer) {
      this.scheduleProfsContainer.style.display = 'none';
      this.scheduleProfsContainer.innerHTML = '';
    }

    // Resetear breadcrumbs
    if (this.chipStep1) this.chipStep1.className = 'step-chip active';
    if (this.chipStep2) this.chipStep2.className = 'step-chip';
    if (this.chipStep3) this.chipStep3.className = 'step-chip';

    // Ocultar barra flotante de confirmación de Pantalla 3
    if (this.scheduleSummaryBar) this.scheduleSummaryBar.style.display = 'none';

    // Resetear slots y barra de confirmación en Pantalla 4
    if (this.calendarConfirmBar) this.calendarConfirmBar.style.display = 'none';
    document.querySelectorAll('.slot-card').forEach(c => {
      c.classList.remove('state-selected');
      if (c.classList.contains('state-available')) {
        const badge = c.querySelector('.slot-badge');
        if (badge) badge.textContent = '🟢 Libre';
      }
    });
  }

  // =========================================================================
  // PANTALLA 5: MIS LECCIONES Y CANCELACIÓN ATÓMICA (Jira M01-15, M01-16)
  // =========================================================================

  async navigateToLessons(preferredTab = 'upcoming') {
    if (this.viewDashboard) this.viewDashboard.classList.remove('active');
    if (this.viewSchedule) this.viewSchedule.style.display = 'none';
    if (this.viewCalendar) this.viewCalendar.style.display = 'none';
    if (this.viewProfile) this.viewProfile.style.display = 'none';
    if (this.viewSuccessTicket) this.viewSuccessTicket.style.display = 'none';
    if (this.viewLessons) this.viewLessons.style.display = 'block';
    if (this.appBottomNav) this.appBottomNav.style.display = 'flex';
    this.updateActiveNav('nav-lessons');
    window.scrollTo({ top: 0, behavior: 'smooth' });

    this.switchLessonsTab(preferredTab);
    await this.loadStudentLessons();
  }

  switchLessonsTab(tab) {
    if (tab === 'upcoming') {
      if (this.tabLessonsUpcoming) {
        this.tabLessonsUpcoming.classList.add('active');
        this.tabLessonsUpcoming.setAttribute('aria-selected', 'true');
      }
      if (this.tabLessonsHistory) {
        this.tabLessonsHistory.classList.remove('active');
        this.tabLessonsHistory.setAttribute('aria-selected', 'false');
      }
      if (this.panelLessonsUpcoming) this.panelLessonsUpcoming.style.display = 'flex';
      if (this.panelLessonsHistory) this.panelLessonsHistory.style.display = 'none';
    } else {
      if (this.tabLessonsUpcoming) {
        this.tabLessonsUpcoming.classList.remove('active');
        this.tabLessonsUpcoming.setAttribute('aria-selected', 'false');
      }
      if (this.tabLessonsHistory) {
        this.tabLessonsHistory.classList.add('active');
        this.tabLessonsHistory.setAttribute('aria-selected', 'true');
      }
      if (this.panelLessonsUpcoming) this.panelLessonsUpcoming.style.display = 'none';
      if (this.panelLessonsHistory) this.panelLessonsHistory.style.display = 'flex';
    }
  }

  async loadStudentLessons() {
    const student = this.sessionManager.getStudent();
    if (!student) return;

    if (this.panelLessonsUpcoming) {
      this.panelLessonsUpcoming.innerHTML = `
        <div style="padding: 32px 16px; text-align: center; color: #0284c7; font-size: 0.85rem; width: 100%;">
          <span class="spinner" style="border-top-color: #0284c7; display:inline-block; margin-bottom:8px;"></span><br>
          Cargando tus lecciones y horarios...
        </div>
      `;
    }

    try {
      const studentId = student.id || 1;
      const res = await fetch(`${this.lessonsUrl}?action=list&student_id=${studentId}`);
      const data = await res.json();

      if (res.ok && data.success) {
        this.lessonsState.proximas = data.proximas || [];
        this.lessonsState.historial = data.historial || [];

        if (this.countUpcoming) this.countUpcoming.textContent = this.lessonsState.proximas.length;
        if (this.countHistory) this.countHistory.textContent = this.lessonsState.historial.length;

        this.renderLessonsLists();
      } else {
        this.showToast(data.message || 'Error al obtener lecciones', 'error');
      }
    } catch (err) {
      console.warn("Fallo de red al cargar lecciones:", err);
      this.showToast('Error de conexión al cargar lecciones', 'error');
    }
  }

  renderLessonsLists() {
    // 1. Renderizar Lecciones Próximas (M01-15)
    if (this.panelLessonsUpcoming) {
      if (this.lessonsState.proximas.length === 0) {
        this.panelLessonsUpcoming.innerHTML = `
          <div class="empty-lessons-box">
            <div class="empty-lessons-icon">📅</div>
            <h4 class="empty-lessons-title">No tienes lecciones próximas</h4>
            <p class="empty-lessons-desc">Reserva tu clase de conversación con nuestros profesores nativos certificados.</p>
            <button type="button" class="btn-meet-join" id="btn-schedule-from-lessons" style="width: auto; padding: 10px 22px; margin: 0 auto;">
              <span>Agendar Lección Ahora</span>
              <span style="font-size: 1rem;">➔</span>
            </button>
          </div>
        `;
        const btnAgendar = document.getElementById('btn-schedule-from-lessons');
        if (btnAgendar) {
          btnAgendar.addEventListener('click', () => this.navigateToSchedule());
        }
      } else {
        this.panelLessonsUpcoming.innerHTML = this.lessonsState.proximas.map(l => {
          const initials = l.profesor.nombre.split(' ').map(n => n[0]).join('').substring(0, 2);
          return `
            <div class="lesson-item-card" data-lesson-id="${l.id}">
              <div class="lesson-item-top">
                <span class="lesson-course-badge">${l.curso.codigo} • ${l.nivel.codigo}</span>
                <span class="status-pill programada">● Programada</span>
              </div>
              <h4 class="lesson-course-title">${l.curso.nombre}</h4>
              <div class="lesson-time-row">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>${l.fecha} • ${l.hora_rango}</span>
              </div>
              <div class="lesson-teacher-box">
                <div class="teacher-mini-avatar">${initials}</div>
                <div class="teacher-mini-info">
                  <div class="teacher-mini-name">${l.profesor.nombre}</div>
                  <div class="teacher-mini-spec">${l.profesor.especialidad} • ⭐ ${l.profesor.rating}</div>
                </div>
              </div>
              <div class="lesson-card-actions">
                <a href="${l.enlace_sesion}" target="_blank" rel="noopener noreferrer" class="btn-meet-join">
                  <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                  </svg>
                  <span>Entrar a Clase</span>
                </a>
                <button type="button" class="btn-cancel-lesson-trigger" data-cancel-id="${l.id}">
                  <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                  <span>Cancelar</span>
                </button>
              </div>
            </div>
          `;
        }).join('');

        // Wire cancel triggers
        this.panelLessonsUpcoming.querySelectorAll('.btn-cancel-lesson-trigger').forEach(btn => {
          btn.addEventListener('click', () => {
            const lid = parseInt(btn.getAttribute('data-cancel-id'), 10);
            const lesson = this.lessonsState.proximas.find(item => item.id === lid);
            if (lesson) this.promptCancelLesson(lesson);
          });
        });
      }
    }

    // 2. Renderizar Historial de Lecciones
    if (this.panelLessonsHistory) {
      if (this.lessonsState.historial.length === 0) {
        this.panelLessonsHistory.innerHTML = `
          <div class="empty-lessons-box">
            <div class="empty-lessons-icon">📜</div>
            <h4 class="empty-lessons-title">Sin historial de clases</h4>
            <p class="empty-lessons-desc">Tus clases concluidas o canceladas aparecerán registradas aquí.</p>
          </div>
        `;
      } else {
        this.panelLessonsHistory.innerHTML = this.lessonsState.historial.map(l => {
          const initials = l.profesor.nombre.split(' ').map(n => n[0]).join('').substring(0, 2);
          const isCompleted = l.estado === 'COMPLETADA';
          const statusClass = isCompleted ? 'completada' : 'cancelada';
          const statusText = isCompleted ? '✓ Completada' : '✕ Cancelada';

          return `
            <div class="lesson-item-card" style="opacity: ${isCompleted ? '0.92' : '0.75'};">
              <div class="lesson-item-top">
                <span class="lesson-course-badge">${l.curso.codigo} • ${l.nivel.codigo}</span>
                <span class="status-pill ${statusClass}">${statusText}</span>
              </div>
              <h4 class="lesson-course-title">${l.curso.nombre}</h4>
              <div class="lesson-time-row" style="color: #64748b;">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>${l.fecha} • ${l.hora_rango}</span>
              </div>
              <div class="lesson-teacher-box" style="margin-bottom: 0;">
                <div class="teacher-mini-avatar" style="background: #94a3b8;">${initials}</div>
                <div class="teacher-mini-info">
                  <div class="teacher-mini-name">${l.profesor.nombre}</div>
                  <div class="teacher-mini-spec">${l.profesor.especialidad}</div>
                </div>
              </div>
            </div>
          `;
        }).join('');
      }
    }
  }

  promptCancelLesson(lesson) {
    this.currentCancelTarget = lesson;
    if (this.cancelProfName) this.cancelProfName.textContent = lesson.profesor.nombre;
    if (this.cancelLessonDate) this.cancelLessonDate.textContent = lesson.fecha;
    if (this.cancelLessonTime) this.cancelLessonTime.textContent = lesson.hora_rango;
    if (this.cancelLessonCourse) this.cancelLessonCourse.textContent = `${lesson.curso.nombre} (${lesson.nivel.codigo})`;
    if (this.modalCancelLesson) this.modalCancelLesson.classList.add('active');
  }

  async executeLessonCancellation() {
    if (!this.currentCancelTarget) return;

    const student = this.sessionManager.getStudent();
    const lesson = this.currentCancelTarget;
    this.setButtonLoading(this.btnConfirmCancelLesson, true, 'Liberando horario...');

    try {
      const res = await fetch(this.lessonsUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'cancel',
          lesson_id: lesson.id,
          uuid: lesson.uuid,
          student_id: student ? (student.id || 1) : 1
        })
      });

      const data = await res.json();

      if (res.ok && data.success) {
        if (this.modalCancelLesson) this.modalCancelLesson.classList.remove('active');
        this.currentCancelTarget = null;
        this.showToast('✅ Lección cancelada con éxito y horario liberado para otros alumnos.', 'success');

        // Recargar listado de lecciones inmediatamente
        await this.loadStudentLessons();

        // Recargar dashboard para actualizar métricas de pendientes / horas
        if (student) {
          this.renderDashboard(student);
        }
      } else {
        this.showToast(data.message || 'Error al cancelar lección', 'error');
      }
    } catch (err) {
      console.error("Error en cancelación:", err);
      this.showToast('Error de conexión al cancelar lección', 'error');
    } finally {
      this.setButtonLoading(this.btnConfirmCancelLesson, false, 'Sí, Cancelar Lección y Liberar Horario');
    }
  }

  // =========================================================================
  // PANTALLA 6: PERFIL DEL ESTUDIANTE (Jira M01-6, M01-7)
  // =========================================================================

  async navigateToProfile() {
    if (this.viewDashboard) this.viewDashboard.classList.remove('active');
    if (this.viewSchedule) this.viewSchedule.style.display = 'none';
    if (this.viewCalendar) this.viewCalendar.style.display = 'none';
    if (this.viewLessons) this.viewLessons.style.display = 'none';
    if (this.viewSuccessTicket) this.viewSuccessTicket.style.display = 'none';
    if (this.viewProfile) this.viewProfile.style.display = 'block';
    if (this.appBottomNav) this.appBottomNav.style.display = 'flex';
    this.updateActiveNav('nav-profile');
    window.scrollTo({ top: 0, behavior: 'smooth' });

    await this.loadStudentProfile(false);
  }

  async loadStudentProfile(showToastOnSuccess = false) {
    const student = this.sessionManager.getStudent();
    if (!student) return;

    try {
      const studentId = student.id || 1;
      const res = await fetch(`${this.profileUrl}?action=get&student_id=${studentId}`);
      const data = await res.json();

      if (res.ok && data.success) {
        const s = data.student;
        const n = data.nivel_info;
        const stats = data.estadisticas;

        // Actualizar Hero Card
        const fullName = s.nombre || 'Estudiante';
        const initials = fullName.split(' ').filter(Boolean).map(w => w[0]).join('').substring(0, 2).toUpperCase() || 'ST';
        if (this.profileAvatarLarge) this.profileAvatarLarge.textContent = initials;
        if (this.profileHeroName) this.profileHeroName.textContent = fullName;
        if (this.profileHeroMatricula) this.profileHeroMatricula.textContent = s.matricula || s.username || 'EMD-2026-0001';
        if (this.profileHeroEmail) this.profileHeroEmail.textContent = s.email;

        // Actualizar CEFR Level Card
        if (this.profileCefrCode) this.profileCefrCode.textContent = `Nivel: ${n.codigo}`;
        if (this.profileCefrName) this.profileCefrName.textContent = n.nombre;
        if (this.profileCefrDesc) this.profileCefrDesc.textContent = n.descripcion;
        if (this.profileCefrPercent) this.profileCefrPercent.textContent = `${n.progreso_porcentaje}%`;
        if (this.profileCefrFill) this.profileCefrFill.style.width = `${n.progreso_porcentaje}%`;
        if (this.profileCefrNext) this.profileCefrNext.textContent = n.siguiente_nivel;

        // Actualizar Stats
        if (this.profileStatCompleted) this.profileStatCompleted.textContent = stats.clases_completadas;
        if (this.profileStatHours) this.profileStatHours.textContent = `${stats.horas_acumuladas}h`;
        if (this.profileStatPending) this.profileStatPending.textContent = stats.clases_pendientes;

        // Rellenar formulario editable
        if (this.profileInputName) this.profileInputName.value = s.nombre || '';
        if (this.profileInputPhone) this.profileInputPhone.value = s.telefono || '';
        if (this.profileInputEmail) this.profileInputEmail.value = s.email || '';
        if (this.profileInputMatricula) this.profileInputMatricula.value = s.matricula || s.username || '';

        if (showToastOnSuccess) {
          this.showToast('✅ Datos del perfil sincronizados en tiempo real', 'success');
        }
      } else {
        this.showToast(data.message || 'Error al obtener perfil', 'error');
      }
    } catch (err) {
      console.warn("Fallo de red al cargar perfil:", err);
      this.showToast('Error de conexión al cargar datos del perfil', 'error');
    }
  }

  async handleSaveProfile(e) {
    if (e && e.preventDefault) e.preventDefault();

    const student = this.sessionManager.getStudent();
    if (!student) return;

    const nombre = this.profileInputName ? this.profileInputName.value.trim() : '';
    const telefono = this.profileInputPhone ? this.profileInputPhone.value.trim() : '';

    if (!nombre || nombre.length < 3) {
      this.showToast('El nombre completo debe tener al menos 3 caracteres', 'warning');
      return;
    }

    this.setButtonLoading(this.btnSaveProfile, true, 'Guardando cambios...');

    try {
      const res = await fetch(this.profileUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'update',
          student_id: student.id || 1,
          nombre: nombre,
          telefono: telefono
        })
      });

      const data = await res.json();

      if (res.ok && data.success) {
        // Actualizar el SINGLETON con el nuevo objeto de estudiante
        this.sessionManager.setSession(data.student, this.sessionManager.getToken());

        // Actualizar Hero y Formulario
        await this.loadStudentProfile(false);

        // Actualizar Dashboard
        this.renderDashboard(data.student);

        this.showToast('🎉 ¡Perfil actualizado exitosamente!', 'success');
      } else {
        this.showToast(data.message || 'Error al actualizar perfil', 'error');
      }
    } catch (err) {
      console.error("Error al guardar perfil:", err);
      this.showToast('Error de conexión al actualizar perfil', 'error');
    } finally {
      this.setButtonLoading(this.btnSaveProfile, false, 'Guardar Cambios de Perfil');
    }
  }

  copyMatriculaToClipboard() {
    const matriculaText = this.profileHeroMatricula ? this.profileHeroMatricula.textContent.trim() : '';
    if (!matriculaText) return;

    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(matriculaText).then(() => {
        this.showToast(`📋 Matrícula ${matriculaText} copiada al portapapeles`, 'info');
      }).catch(() => {
        this.showToast(`Matrícula: ${matriculaText}`, 'info');
      });
    } else {
      this.showToast(`Matrícula: ${matriculaText}`, 'info');
    }
  }

  // =========================================================================
  // PANTALLA 7: CONFIRMACIÓN EXITOSA (TICKET) Y COLISIÓN 409 (Jira M01-17, M01-18)
  // =========================================================================

  renderSuccessTicket(lesson, course, level, prof, slot, date, student) {
    if (this.viewDashboard) this.viewDashboard.classList.remove('active');
    if (this.viewSchedule) this.viewSchedule.style.display = 'none';
    if (this.viewCalendar) this.viewCalendar.style.display = 'none';
    if (this.viewLessons) this.viewLessons.style.display = 'none';
    if (this.viewProfile) this.viewProfile.style.display = 'none';
    if (this.viewSuccessTicket) this.viewSuccessTicket.style.display = 'block';
    if (this.appBottomNav) this.appBottomNav.style.display = 'flex';
    window.scrollTo({ top: 0, behavior: 'smooth' });

    // Código de Reserva
    if (this.ticketRefCode) this.ticketRefCode.textContent = lesson.uuid || `LEC-${lesson.id}`;

    // Programa y Nivel
    if (this.ticketCourseLevel) {
      this.ticketCourseLevel.textContent = `${course.nombre} • ${level.codigo} ${level.nombre}`;
    }

    // Profesor
    const profInitials = prof.nombre.split(' ').filter(Boolean).slice(-2).map(w => w[0]).join('').toUpperCase() || 'PR';
    if (this.ticketProfAvatar) this.ticketProfAvatar.textContent = profInitials;
    if (this.ticketProfName) this.ticketProfName.textContent = prof.nombre;
    if (this.ticketProfSpec) this.ticketProfSpec.textContent = `${prof.especialidad} • ⭐ ${prof.rating.toFixed(2)}`;

    // Fecha y Horario
    if (this.ticketDateTime) {
      this.ticketDateTime.textContent = `${date} • ${slot.hora_rango}`;
    }

    // Estudiante
    if (this.ticketStudentName) {
      this.ticketStudentName.textContent = `${student.nombre} (Matrícula: ${student.matricula || student.username})`;
    }

    // Enlace de Google Meet
    const meetUrl = lesson.enlace_sesion || 'https://meet.google.com/emd-live-room';
    if (this.ticketMeetUrl) this.ticketMeetUrl.textContent = meetUrl;
    if (this.ticketBtnOpenMeet) this.ticketBtnOpenMeet.href = meetUrl;

    this.showToast('🎉 ¡Lección Confirmada y Voucher Digital emitido!', 'success');
  }

  showCollisionModal(prof, slot, date) {
    if (this.collisionSlotLabel) {
      this.collisionSlotLabel.textContent = `${date} de ${slot.hora_rango}`;
    }
    if (this.collisionProfLabel) {
      this.collisionProfLabel.textContent = `${prof.nombre}`;
    }
    if (this.modalCollisionAlert) {
      this.modalCollisionAlert.classList.add('active');
    }
    this.showToast('⚠️ Conflicto Concurrente: Este cupo ya no está disponible.', 'warning');
  }

  async dismissCollisionAndReload() {
    if (this.modalCollisionAlert) {
      this.modalCollisionAlert.classList.remove('active');
    }

    // Deseleccionar slot previo y ocultar barra de confirmación
    this.bookingState.selectedSlot = null;
    if (this.calendarConfirmBar) this.calendarConfirmBar.style.display = 'none';

    // Recargar slots forzadamente desde el backend para que se pinte en GRIS
    if (this.bookingState.selectedDate) {
      await this.loadSlotsForDate(this.bookingState.selectedDate);
    }

    this.showToast('Horarios actualizados en tiempo real. Elige un cupo libre (verde).', 'info');
  }

  copyMeetLinkToClipboard() {
    const url = this.ticketMeetUrl ? this.ticketMeetUrl.textContent.trim() : '';
    if (!url) return;

    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(url).then(() => {
        this.showToast('📋 Enlace de Google Meet copiado al portapapeles', 'info');
      }).catch(() => {
        this.showToast(`Enlace: ${url}`, 'info');
      });
    } else {
      this.showToast(`Enlace: ${url}`, 'info');
    }
  }

  setButtonLoading(btn, isLoading, text) {
    if (!btn) return;
    btn.disabled = isLoading;
    if (isLoading) {
      btn.innerHTML = `<span class="spinner"></span> <span>${text}</span>`;
    } else {
      btn.innerHTML = `<span>${text}</span>`;
    }
  }

  showToast(message, type = 'info') {
    if (!this.toastContainer) return;
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    let iconSvg = '';
    if (type === 'success') {
      iconSvg = '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
    } else if (type === 'error') {
      iconSvg = '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
    } else if (type === 'warning') {
      iconSvg = '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>';
    } else {
      iconSvg = '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
    }

    toast.innerHTML = `<div style="display:flex;align-items:center;gap:10px;">${iconSvg}<span>${message}</span></div>`;

    this.toastContainer.appendChild(toast);
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(-14px)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 4200);
  }
}

// Inicialización de la aplicación
document.addEventListener('DOMContentLoaded', () => {
  new AuthUIController();
});
