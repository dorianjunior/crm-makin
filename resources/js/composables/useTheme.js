import { ref, onMounted, watch } from 'vue';

const THEME_KEY = 'app-theme';
const THEMES = {
    LIGHT: 'light',
    DARK: 'dark',
};

export function useTheme() {
    const currentTheme = ref(THEMES.LIGHT);

    // Load theme from localStorage or system preference
    const loadTheme = () => {
        const stored = localStorage.getItem(THEME_KEY);

        if (stored && Object.values(THEMES).includes(stored)) {
            currentTheme.value = stored;
        } else {
            // Check system preference
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            currentTheme.value = prefersDark ? THEMES.DARK : THEMES.LIGHT;
        }

        applyTheme(currentTheme.value);
    };

    // Apply theme to document
    const applyTheme = (theme) => {
        document.documentElement.setAttribute('data-theme', theme);
    };

    // Save theme to localStorage
    const saveTheme = (theme) => {
        localStorage.setItem(THEME_KEY, theme);
        currentTheme.value = theme;
        applyTheme(theme);
    };

    // Toggle between themes
    const toggleTheme = () => {
        const newTheme = currentTheme.value === THEMES.LIGHT ? THEMES.DARK : THEMES.LIGHT;
        saveTheme(newTheme);
    };

    // Set specific theme
    const setTheme = (theme) => {
        if (Object.values(THEMES).includes(theme)) {
            saveTheme(theme);
        } else {
            console.warn(`Invalid theme: ${theme}. Use 'light' or 'dark'.`);
        }
    };

    // Check if current theme is dark
    const isDark = () => {
        return currentTheme.value === THEMES.DARK;
    };

    // Check if current theme is light
    const isLight = () => {
        return currentTheme.value === THEMES.LIGHT;
    };

    // Watch for system theme changes
    const watchSystemTheme = () => {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

        const handleChange = (e) => {
            // Only auto-update if user hasn't manually set a preference
            if (!localStorage.getItem(THEME_KEY)) {
                const newTheme = e.matches ? THEMES.DARK : THEMES.LIGHT;
                currentTheme.value = newTheme;
                applyTheme(newTheme);
            }
        };

        mediaQuery.addEventListener('change', handleChange);

        return () => {
            mediaQuery.removeEventListener('change', handleChange);
        };
    };

    // Initialize on mount
    onMounted(() => {
        loadTheme();
        watchSystemTheme();
    });

    // Watch for theme changes to apply them
    watch(currentTheme, (newTheme) => {
        applyTheme(newTheme);
    });

    return {
        currentTheme,
        isDark,
        isLight,
        toggleTheme,
        setTheme,
        THEMES,
    };
}
