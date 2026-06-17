let types = [];
let loaded = false;

export async function loadTypes() {
    if (loaded) return types;

    const res = await fetch('/api/v1/public/pokemon/types');
    types = await res.json();
    loaded = true;

    return types;
}

export function getTypes() {
    return types;
}