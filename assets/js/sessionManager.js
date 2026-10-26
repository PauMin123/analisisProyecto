/**
 * ============================================================================
 * PATRÓN DE DISEÑO: SINGLETON (GoF)
 * Componente: StudentSessionManager
 * 
 * Justificación Técnica (Rúbrica - 3.0 pts):
 * Provee un punto de acceso global y una única instancia para preservar la
 * integridad del estado de la sesión del estudiante, token de autenticación
 * y sincronización de caché local en el dispositivo móvil.
 * ============================================================================
 */

export class StudentSessionManager {
  static instance = null;
  
  constructor() {
    if (StudentSessionManager.instance) {
      throw new Error("StudentSessionManager es un Singleton. Use StudentSessionManager.getInstance()");
    }
    this._student = null;
    this._token = null;
    this._isOnline = navigator.onLine;
    this._initStorage();
    this._setupNetworkListeners();
  }

  /**
   * Punto de acceso global único (Singleton)
   * @returns {StudentSessionManager}
   */
  static getInstance() {
    if (!StudentSessionManager.instance) {
      StudentSessionManager.instance = new StudentSessionManager();
    }
    return StudentSessionManager.instance;
  }

  /**
   * Carga sesión guardada desde el almacenamiento local persistente
   */
  _initStorage() {
    try {
      const stored = localStorage.getItem('english_schedule_session');
      if (stored) {
        const data = JSON.parse(stored);
        this._student = data.student || null;
        this._token = data.token || null;
      }
    } catch (e) {
      console.warn("No se pudo cargar la sesión persistida:", e);
    }
  }

  /**
   * Monitoreo de conectividad (Reglas de Oro de Larsson para móviles)
   */
  _setupNetworkListeners() {
    window.addEventListener('online', () => {
      this._isOnline = true;
      window.dispatchEvent(new CustomEvent('app:network-status', { detail: { online: true } }));
    });
    window.addEventListener('offline', () => {
      this._isOnline = false;
      window.dispatchEvent(new CustomEvent('app:network-status', { detail: { online: false } }));
    });
  }

  /**
   * Establece una nueva sesión autenticada
   */
  setSession(student, token) {
    this._student = student;
    this._token = token;
    try {
      localStorage.setItem('english_schedule_session', JSON.stringify({
        student: this._student,
        token: this._token,
        timestamp: Date.now()
      }));
    } catch (e) {
      console.error("Error guardando sesión:", e);
    }
    window.dispatchEvent(new CustomEvent('app:session-changed', { detail: { student: this._student } }));
  }

  /**
   * Obtiene los datos del estudiante actual
   */
  getStudent() {
    return this._student;
  }

  /**
   * Obtiene el token de autenticación
   */
  getToken() {
    return this._token;
  }

  /**
   * Verifica si hay un estudiante con sesión activa
   */
  isAuthenticated() {
    return this._student !== null && this._token !== null;
  }

  /**
   * Cierra la sesión activa y limpia el almacenamiento
   */
  clearSession() {
    this._student = null;
    this._token = null;
    localStorage.removeItem('english_schedule_session');
    window.dispatchEvent(new CustomEvent('app:session-cleared'));
  }

  /**
   * Indica si el dispositivo tiene conexión a internet
   */
  isOnline() {
    return this._isOnline;
  }
}
