/**
 * Application Constants
 * 
 * Centralized constants for the application.
 * Improves code readability and maintainability.
 */

// Toast notification durations (milliseconds)
const TOAST_SUCCESS_DURATION = 1800; // 1.8 seconds
const TOAST_ERROR_DURATION = 2500;   // 2.5 seconds
const TOAST_WARNING_DURATION = 3000; // 3 seconds
const TOAST_INFO_DURATION = 2000;    // 2 seconds

// Field validation lengths (characters)
const MAX_LENGTH_NOMBRES = 255;
const MAX_LENGTH_APELLIDOS = 255;
const MAX_LENGTH_EMAIL = 255;
const MAX_LENGTH_TELEFONO = 50;
const MAX_LENGTH_DIRECCION = 500;
const MAX_LENGTH_CIUDAD = 100;
const MAX_LENGTH_COMENTARIOS = 1000;
const MAX_LENGTH_CODIGO = 100;
const MAX_LENGTH_FOTO_PATH = 255;

const MIN_LENGTH_NOMBRES = 2;
const MIN_LENGTH_APELLIDOS = 2;

// Age constraints
const MIN_AGE = 18;
const MAX_AGE = 100;

// File upload constraints
const MAX_FILE_SIZE_MB = 5;
const MAX_FILE_SIZE_BYTES = MAX_FILE_SIZE_MB * 1024 * 1024; // 5MB
const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
const ALLOWED_IMAGE_EXTENSIONS = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];

// File magic numbers for validation
const MAGIC_NUMBERS = {
    JPEG: 'ffd8ff',
    PNG: '89504e47',
    GIF: '47494638',
    WEBP: '52494646'
};

// DataTables configuration
const DATATABLE_PAGE_LENGTH = 10;
const DATATABLE_PAGE_LENGTH_OPTIONS = [10, 25, 50, 100];
const DATATABLE_LANGUAGE_ES = 'assets/i18n/es.json';
const DATATABLE_LANGUAGE_EN = 'assets/i18n/en.json';

// UI z-index levels
const Z_INDEX_LOADING_OVERLAY = 9999;
const Z_INDEX_MODAL = 1050;
const Z_INDEX_TOAST = 9000;

// API response codes
const HTTP_OK = 200;
const HTTP_CREATED = 201;
const HTTP_BAD_REQUEST = 400;
const HTTP_UNAUTHORIZED = 401;
const HTTP_FORBIDDEN = 403;
const HTTP_NOT_FOUND = 404;
const HTTP_UNPROCESSABLE_ENTITY = 422;
const HTTP_INTERNAL_SERVER_ERROR = 500;

// Broadcast Channel names
const CHANNEL_EMPLOYEE_EDIT = 'lotificaciones-employee-edit';
const CHANNEL_THEME_CHANGE = 'lotificaciones-theme';

// Theme palettes
const THEME_PALETTES = ['blue', 'teal', 'violet'];
const DEFAULT_THEME = 'blue';

// Language codes
const LANG_SPANISH = 'es';
const LANG_ENGLISH = 'en';
const DEFAULT_LANGUAGE = LANG_SPANISH;

// LocalStorage keys
const STORAGE_KEY_LANGUAGE = 'language';
const STORAGE_KEY_THEME = 'theme-palette';
const STORAGE_KEY_PAGE_LENGTH = 'dt_pageLength';
const STORAGE_KEY_COLUMN_VISIBILITY = 'dt_colVis';

// SweetAlert2 positions
const TOAST_POSITION = 'top-end';

// Debounce delays (milliseconds)
const DEBOUNCE_SEARCH = 300;
const DEBOUNCE_RESIZE = 200;

// Animation durations (milliseconds)
const FADE_DURATION = 200;
const SLIDE_DURATION = 300;

// Gender options
const GENDER_MASCULINO = 'Masculino';
const GENDER_FEMENINO = 'Femenino';
const GENDER_OTRO = 'Otro';

// Date formats
const DATE_FORMAT_SQL = 'YYYY-MM-DD';
const DATE_FORMAT_DISPLAY = 'DD/MM/YYYY';
const DATETIME_FORMAT_SQL = 'YYYY-MM-DD HH:mm:ss';

// Thumbnail dimensions
const THUMBNAIL_WIDTH = 50;
const THUMBNAIL_HEIGHT = 50;

// Photo display dimensions  
const PHOTO_MAX_WIDTH = 800;
const PHOTO_MAX_HEIGHT = 800;

// Export this as a module if using modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        // Toast durations
        TOAST_SUCCESS_DURATION,
        TOAST_ERROR_DURATION,
        TOAST_WARNING_DURATION,
        TOAST_INFO_DURATION,
        
        // Field lengths
        MAX_LENGTH_NOMBRES,
        MAX_LENGTH_APELLIDOS,
        MAX_LENGTH_EMAIL,
        MAX_LENGTH_TELEFONO,
        MAX_LENGTH_DIRECCION,
        MAX_LENGTH_CIUDAD,
        MAX_LENGTH_COMENTARIOS,
        MAX_LENGTH_CODIGO,
        MAX_LENGTH_FOTO_PATH,
        MIN_LENGTH_NOMBRES,
        MIN_LENGTH_APELLIDOS,
        
        // Age
        MIN_AGE,
        MAX_AGE,
        
        // File uploads
        MAX_FILE_SIZE_MB,
        MAX_FILE_SIZE_BYTES,
        ALLOWED_IMAGE_TYPES,
        ALLOWED_IMAGE_EXTENSIONS,
        MAGIC_NUMBERS,
        
        // DataTables
        DATATABLE_PAGE_LENGTH,
        DATATABLE_PAGE_LENGTH_OPTIONS,
        DATATABLE_LANGUAGE_ES,
        DATATABLE_LANGUAGE_EN,
        
        // Z-index
        Z_INDEX_LOADING_OVERLAY,
        Z_INDEX_MODAL,
        Z_INDEX_TOAST,
        
        // HTTP
        HTTP_OK,
        HTTP_CREATED,
        HTTP_BAD_REQUEST,
        HTTP_UNAUTHORIZED,
        HTTP_FORBIDDEN,
        HTTP_NOT_FOUND,
        HTTP_UNPROCESSABLE_ENTITY,
        HTTP_INTERNAL_SERVER_ERROR,
        
        // Channels
        CHANNEL_EMPLOYEE_EDIT,
        CHANNEL_THEME_CHANGE,
        
        // Theme
        THEME_PALETTES,
        DEFAULT_THEME,
        
        // Language
        LANG_SPANISH,
        LANG_ENGLISH,
        DEFAULT_LANGUAGE,
        
        // Storage
        STORAGE_KEY_LANGUAGE,
        STORAGE_KEY_THEME,
        STORAGE_KEY_PAGE_LENGTH,
        STORAGE_KEY_COLUMN_VISIBILITY,
        
        // UI
        TOAST_POSITION,
        DEBOUNCE_SEARCH,
        DEBOUNCE_RESIZE,
        FADE_DURATION,
        SLIDE_DURATION,
        
        // Gender
        GENDER_MASCULINO,
        GENDER_FEMENINO,
        GENDER_OTRO,
        
        // Dates
        DATE_FORMAT_SQL,
        DATE_FORMAT_DISPLAY,
        DATETIME_FORMAT_SQL,
        
        // Images
        THUMBNAIL_WIDTH,
        THUMBNAIL_HEIGHT,
        PHOTO_MAX_WIDTH,
        PHOTO_MAX_HEIGHT
    };
}
