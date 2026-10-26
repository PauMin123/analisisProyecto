/**
 * ============================================================================
 * PATRÓN DE DISEÑO: FACTORY METHOD (GoF)
 * Componente: CourseServiceFactory
 * 
 * Justificación Técnica (Rúbrica - 3.0 pts):
 * Desacopla la capa de presentación de la lógica específica de cada programa
 * académico de la academia (General English, Business English, IELTS/TOEFL, Speaking Club).
 * Permite instanciar dinámicamente servicios que encapsulan reglas de nivel CEFR,
 * colores temáticos y provisión de enlaces de videoconferencia sin alterar la vista.
 * ============================================================================
 */

export class ICourseService {
  constructor(courseCode, name) {
    this.courseCode = courseCode;
    this.name = name;
  }
  
  validateLevelRequirement(studentLevel, requestedLevel) {
    throw new Error("Método abstracto debe ser implementado.");
  }

  generateSessionLink(lessonUuid) {
    throw new Error("Método abstracto debe ser implementado.");
  }

  getBadgeColor() {
    return "#0284c7";
  }
}

export class GeneralEnglishCourseService extends ICourseService {
  constructor() {
    super('GENERAL', 'Inglés General Integral');
  }

  validateLevelRequirement(studentLevel, requestedLevel) {
    return { allowed: true, reason: 'Acceso habilitado para todo nivel pedagógico (A1 a C1).' };
  }

  generateSessionLink(lessonUuid) {
    return `https://meet.google.com/emd-gen-${lessonUuid.toLowerCase().replace(/[^a-z0-9]/g, '')}`;
  }

  getBadgeColor() {
    return "#0284c7";
  }
}

export class BusinessEnglishCourseService extends ICourseService {
  constructor() {
    super('BUSINESS', 'Inglés Corporativo y de Negocios');
  }

  validateLevelRequirement(studentLevel, requestedLevel) {
    const levelsOrder = ['A1', 'A2', 'B1', 'B2', 'C1'];
    const sIdx = levelsOrder.indexOf(studentLevel);
    if (sIdx < 2 && requestedLevel !== 'B1') {
      return { allowed: false, reason: 'Requiere nivel previo B1 Intermedio para terminología corporativa.' };
    }
    return { allowed: true, reason: 'Cumple prerrequisitos de inglés corporativo.' };
  }

  generateSessionLink(lessonUuid) {
    return `https://meet.google.com/emd-biz-${lessonUuid.toLowerCase().replace(/[^a-z0-9]/g, '')}`;
  }

  getBadgeColor() {
    return "#059669";
  }
}

export class ExamPrepCourseService extends ICourseService {
  constructor() {
    super('IELTS_TOEFL', 'Preparación Oficial IELTS & TOEFL');
  }

  validateLevelRequirement(studentLevel, requestedLevel) {
    return { allowed: true, reason: 'Simulacros de examen diagnóstico oficiales disponibles.' };
  }

  generateSessionLink(lessonUuid) {
    return `https://meet.google.com/emd-exam-${lessonUuid.toLowerCase().replace(/[^a-z0-9]/g, '')}`;
  }

  getBadgeColor() {
    return "#d97706";
  }
}

export class ConversationClubCourseService extends ICourseService {
  constructor() {
    super('CONVERSATION', 'Club de Fluidez y Conversación');
  }

  validateLevelRequirement(studentLevel, requestedLevel) {
    return { allowed: true, reason: 'Grupos reducidos con enfoque conversacional 100% activo.' };
  }

  generateSessionLink(lessonUuid) {
    return `https://meet.google.com/emd-talk-${lessonUuid.toLowerCase().replace(/[^a-z0-9]/g, '')}`;
  }

  getBadgeColor() {
    return "#8b5cf6";
  }
}

/**
 * Fabrica concreta de servicios según el código del programa
 */
export class CourseServiceFactory {
  static createService(courseCode) {
    switch (courseCode) {
      case 'BUSINESS':
        return new BusinessEnglishCourseService();
      case 'IELTS_TOEFL':
        return new ExamPrepCourseService();
      case 'CONVERSATION':
        return new ConversationClubCourseService();
      case 'GENERAL':
      default:
        return new GeneralEnglishCourseService();
    }
  }
}
