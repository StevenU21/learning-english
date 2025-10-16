export function withQuery(url: string): string {
    try {
        const qs = window.location.search || '';
        if (!qs) return url;
        // Avoid duplicate '?'
        if (url.includes('?')) {
            const extra = qs.startsWith('?') ? qs.slice(1) : qs;
            return url + (extra ? '&' + extra : '');
        }
        return url + qs;
    } catch {
        return url;
    }
}
