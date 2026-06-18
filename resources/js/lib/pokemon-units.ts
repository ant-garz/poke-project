export function formatHeight(dm: number) {
    const meters = dm / 10;
    const feetTotal = meters * 3.28084;

    const feet = Math.floor(feetTotal);
    const inches = Math.round((feetTotal - feet) * 12);

    return {
        meters: meters.toFixed(1) + ' m',
        feet: `${feet} ft ${inches} in`
    };
}

export function formatWeight(hg: number) {
    const kg = hg / 10;
    const lbs = kg * 2.20462;

    return {
        kg: kg.toFixed(1) + ' kg',
        lbs: lbs.toFixed(1) + ' lbs'
    };
}