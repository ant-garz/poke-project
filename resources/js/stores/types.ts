const STORAGE_KEY = 'pokemon_types';

let types = [];
let loaded = false;

export async function loadTypes() {
    if (loaded) {
        return types;
    }

    const cached = localStorage.getItem(STORAGE_KEY);

    if (cached) {
        types = JSON.parse(cached);
        loaded = true;

        return types;
    }

    const res = await fetch('/api/v1/public/pokemon/types');
    types = await res.json();

    localStorage.setItem(STORAGE_KEY, JSON.stringify(types));

    loaded = true;

    return types;
}

export function getTypes() {
    return types;
}

export function clearTypesCache() {
    localStorage.removeItem(STORAGE_KEY);
    types = [];
    loaded = false;
}