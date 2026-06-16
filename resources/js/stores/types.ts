let types = [];

export async function loadTypes() {
    if (types.length) return types;

    const res = await fetch('/api/v1/public/pokemon/types');
    types = await res.json();

    return types;
}

export function getTypes() {
    return types;
}