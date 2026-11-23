(function () {
    const indexesByUrl = new Map();
    const municipalityTypes = new Set([
        'municipality',
        'component_city',
        'highly_urbanized_city',
        'independent_component_city',
    ]);

    async function loadIndex(dataUrl) {
        if (!indexesByUrl.has(dataUrl)) {
            const indexPromise = fetch(dataUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            }).then(async (response) => {
                if (!response.ok) throw new Error(`Address data failed with ${response.status}.`);

                const payload = await response.json();
                if (!Array.isArray(payload.records)) throw new Error('Address data is invalid.');

                const records = payload.records
                    .filter((row) => Array.isArray(row) && row.length >= 4)
                    .map(([type, code, parentCode, name]) => ({
                        type: String(type || ''),
                        code: String(code || ''),
                        parentCode: String(parentCode || ''),
                        name: String(name || ''),
                    }))
                    .filter((record) => record.code && record.name);

                const byCode = new Map();
                const childrenByParent = new Map();
                records.forEach((record) => {
                    byCode.set(record.code, record);
                    if (!childrenByParent.has(record.parentCode)) childrenByParent.set(record.parentCode, []);
                    childrenByParent.get(record.parentCode).push(record);
                });

                return { records, byCode, childrenByParent };
            }).catch((error) => {
                indexesByUrl.delete(dataUrl);
                throw error;
            });

            indexesByUrl.set(dataUrl, indexPromise);
        }

        return indexesByUrl.get(dataUrl);
    }

    function decodeSegment(segment) {
        try {
            return decodeURIComponent(segment);
        } catch (error) {
            return '';
        }
    }

    async function fetchCollection(path, dataUrl) {
        const normalizedPath = String(path || '').replace(/^\/+|\/+$/g, '');
        const segments = normalizedPath.split('/');
        const { records, byCode, childrenByParent } = await loadIndex(dataUrl);

        if (normalizedPath === 'provinces') {
            return records
                .filter((record) => record.type === 'province')
                .map(({ code, name }) => ({ code, name }));
        }

        if (segments[0] === 'regions' && segments[1] === '130000000' && segments[2] === 'cities-municipalities') {
            return (childrenByParent.get('1300000000') || [])
                .filter((record) => municipalityTypes.has(record.type))
                .map(({ code, name }) => ({ code, name }));
        }

        if (segments[0] === 'provinces' && segments[2] === 'cities-municipalities') {
            const provinceCode = decodeSegment(segments[1] || '');
            return (childrenByParent.get(provinceCode) || [])
                .filter((record) => municipalityTypes.has(record.type))
                .map(({ code, name }) => ({ code, name }));
        }

        if (segments[0] === 'cities-municipalities' && segments[2] === 'barangays') {
            const cityCode = decodeSegment(segments[1] || '');
            const directChildren = childrenByParent.get(cityCode) || [];
            const barangays = directChildren.filter((record) => record.type === 'barangay');

            directChildren
                .filter((record) => record.type === 'submunicipality')
                .forEach((submunicipality) => {
                    barangays.push(...(childrenByParent.get(submunicipality.code) || [])
                        .filter((record) => record.type === 'barangay'));
                });

            return barangays.map(({ code, name }) => ({ code, name }));
        }

        if (segments[0] === 'provinces' && segments.length === 2) {
            const provinceCode = decodeSegment(segments[1] || '');
            return byCode.has(provinceCode) ? [{ ...byCode.get(provinceCode) }] : [];
        }

        throw new Error('Unsupported local address lookup.');
    }

    window.ClinicAddressLookup = { fetchCollection };
})();
