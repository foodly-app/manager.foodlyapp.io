import { ref, computed } from 'vue';

const user = ref(null);
const isAuthenticated = computed(() => user.value !== null);

// Initialize from localStorage
const initAuth = () => {
    const storedUser = localStorage.getItem('user');
    if (storedUser) {
        try {
            user.value = JSON.parse(storedUser);
        } catch (e) {
            localStorage.removeItem('user');
        }
    }
};

// Set user
const setUser = (userData) => {
    user.value = userData;
    if (userData) {
        localStorage.setItem('user', JSON.stringify(userData));
    } else {
        localStorage.removeItem('user');
    }
};

// Logout
const logout = async () => {
    try {
        await axios.post('/auth/logout');
    } catch (error) {
        console.error('Logout error:', error);
    } finally {
        setUser(null);
        window.location.href = '/login';
    }
};

// Check if user is authenticated
const checkAuth = () => {
    return isAuthenticated.value;
};

export function useAuth() {
    return {
        user,
        isAuthenticated,
        initAuth,
        setUser,
        logout,
        checkAuth
    };
}
