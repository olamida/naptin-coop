import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import commandPalette from '../command-palette';

const makePalette = (options = {}) => {
    const cp = commandPalette(options);
    cp.$nextTick = (cb) => cb();
    cp.$refs = { input: { focus: vi.fn() } };
    return cp;
};

const stubFetch = (data = []) => {
    const fn = vi.fn().mockImplementation(async () => ({ ok: true, json: async () => data }));
    vi.stubGlobal('fetch', fn);
    return fn;
};

const shortcutEl = (tag, kind, visible = true) => {
    const node = document.createElement(tag);
    node.setAttribute('data-shortcut', kind);
    Object.defineProperty(node, 'offsetParent', { configurable: true, value: visible ? document.body : null });
    document.body.appendChild(node);
    return node;
};

const stubLocationHref = () => {
    const setter = vi.fn();
    Object.defineProperty(window.location, 'href', {
        configurable: true,
        get: () => 'https://example.test/',
        set: setter,
    });
    return setter;
};

beforeEach(() => {
    document.body.innerHTML = '';
    stubFetch();
});

afterEach(() => {
    delete window.location.href;
    vi.unstubAllGlobals();
});

describe('defaults', () => {
    it('falls back to the admin search endpoint and no new-member URL', () => {
        const cp = commandPalette();
        expect(cp.searchUrl).toBe('/command/search');
        expect(cp.newMemberUrl).toBe('');
    });

    it('uses provided option values', () => {
        const cp = commandPalette({ searchUrl: '/my/search', newMemberUrl: '/members/create' });
        expect(cp.searchUrl).toBe('/my/search');
        expect(cp.newMemberUrl).toBe('/members/create');
    });
});

describe('isTypingTarget', () => {
    it('returns true when a modifier key is held', () => {
        const cp = makePalette();
        expect(cp.isTypingTarget({ metaKey: true, target: document.body })).toBe(true);
        expect(cp.isTypingTarget({ ctrlKey: true, target: document.body })).toBe(true);
        expect(cp.isTypingTarget({ altKey: true, target: document.body })).toBe(true);
    });

    it('returns true for inputs, textareas, selects and contenteditable targets', () => {
        const cp = makePalette();
        const input = document.createElement('input');
        const textarea = document.createElement('textarea');
        const select = document.createElement('select');
        const editable = document.createElement('div');
        editable.contentEditable = 'true';
        for (const target of [input, textarea, select, editable]) {
            expect(cp.isTypingTarget({ target })).toBe(true);
        }
    });

    it('returns false for plain page targets', () => {
        const cp = makePalette();
        expect(cp.isTypingTarget({ target: document.body })).toBe(false);
        expect(cp.isTypingTarget({ target: document.documentElement })).toBe(false);
    });
});

describe('firstShortcut', () => {
    it('returns the first visible element for the kind', () => {
        const cp = makePalette();
        const hidden = shortcutEl('form', 'approve', false);
        const visible = shortcutEl('form', 'approve', true);
        expect(cp.firstShortcut('approve')).toBe(visible);
        expect(cp.firstShortcut('approve')).not.toBe(hidden);
    });

    it('falls back to the first element when nothing is visible', () => {
        const cp = makePalette();
        const first = shortcutEl('form', 'approve', false);
        const second = shortcutEl('form', 'approve', false);
        expect(cp.firstShortcut('approve')).toBe(first);
        expect(cp.firstShortcut('approve')).not.toBe(second);
    });

    it('returns null when no element matches the kind', () => {
        const cp = makePalette();
        shortcutEl('form', 'reject', true);
        expect(cp.firstShortcut('approve')).toBeNull();
    });
});

describe('collectShortcuts', () => {
    it('reports whether approve and reject shortcuts exist', () => {
        const cp = makePalette();
        shortcutEl('form', 'approve', true);
        expect(cp.collectShortcuts()).toEqual({ approve: true, reject: false });
    });

    it('reports both false when no shortcuts are present', () => {
        const cp = makePalette();
        expect(cp.collectShortcuts()).toEqual({ approve: false, reject: false });
    });
});

describe('triggerShortcut', () => {
    it('clicks the submit button inside a shortcut form', () => {
        const cp = makePalette();
        const form = shortcutEl('form', 'approve', true);
        const button = document.createElement('button');
        button.setAttribute('type', 'submit');
        form.appendChild(button);
        const click = vi.spyOn(button, 'click').mockImplementation(() => {});
        cp.triggerShortcut('approve');
        expect(click).toHaveBeenCalledTimes(1);
    });

    it('submits a shortcut form that has no submit button', () => {
        const cp = makePalette();
        const form = shortcutEl('form', 'approve', true);
        const submit = vi.spyOn(form, 'requestSubmit').mockImplementation(() => {});
        cp.triggerShortcut('approve');
        expect(submit).toHaveBeenCalledTimes(1);
    });

    it('clicks a non-form shortcut element directly', () => {
        const cp = makePalette();
        const button = shortcutEl('button', 'approve', true);
        const click = vi.spyOn(button, 'click').mockImplementation(() => {});
        cp.triggerShortcut('approve');
        expect(click).toHaveBeenCalledTimes(1);
    });

    it('does nothing when the kind has no element', () => {
        const cp = makePalette();
        expect(() => cp.triggerShortcut('approve')).not.toThrow();
    });
});

describe('openPalette', () => {
    it('opens, resets state and focuses the search input', () => {
        const cp = makePalette();
        cp.query = 'old';
        cp.groups = [{ items: [{ name: 'x' }] }];
        cp.flat = [{ item: { name: 'x' } }];
        cp.selected = 2;
        cp.openPalette();
        expect(cp.open).toBe(true);
        expect(cp.query).toBe('');
        expect(cp.groups).toEqual([]);
        expect(cp.flat).toEqual([]);
        expect(cp.selected).toBe(0);
        expect(cp.$refs.input.focus).toHaveBeenCalledTimes(1);
    });

    it('triggers the initial search only once', async () => {
        const cp = makePalette();
        cp.openPalette();
        expect(cp.queriedOnce).toBe(true);
        expect(fetch).toHaveBeenCalledTimes(1);
        cp.openPalette();
        expect(fetch).toHaveBeenCalledTimes(1);
    });
});

describe('search', () => {
    it('loads results from the search endpoint and reindexes them', async () => {
        const mock = stubFetch([{ key: 'x', items: [{ name: 'a' }] }]);
        const cp = makePalette({ searchUrl: '/my/search' });
        cp.query = 'loan 12';
        await cp.search();
        expect(fetch).toHaveBeenCalledWith('/my/search?q=loan%2012');
        expect(cp.groups).toEqual([{ key: 'x', items: [{ name: 'a' }] }]);
        expect(cp.flat).toEqual([{ gi: 0, ii: 0, item: { name: 'a' } }]);
        expect(cp.loading).toBe(false);
        expect(mock).toHaveBeenCalledTimes(1);
    });
    it('clears state when the request fails', async () => {
        vi.stubGlobal('fetch', vi.fn().mockRejectedValue(new Error('network')));
        const cp = makePalette();
        cp.groups = [{ key: 'x' }];
        cp.flat = [{ item: {} }];
        await cp.search();
        expect(cp.groups).toEqual([]);
        expect(cp.flat).toEqual([]);
        expect(cp.loading).toBe(false);
    });
});

describe('reindex / isSelected', () => {
    it('flattens groups into an ordered index', () => {
        const cp = makePalette();
        cp.groups = [
            { key: 'loans', items: [{ name: 'a' }, { name: 'b' }] },
            { key: 'savings', items: [{ name: 'c' }] },
        ];
        cp.reindex();
        expect(cp.flat).toHaveLength(3);
        expect(cp.flat[0]).toEqual({ gi: 0, ii: 0, item: { name: 'a' } });
        expect(cp.flat[2]).toEqual({ gi: 1, ii: 0, item: { name: 'c' } });
    });

    it('isSelected matches the currently selected item', () => {
        const cp = makePalette();
        const itemA = { name: 'a' };
        const itemB = { name: 'b' };
        cp.groups = [{ items: [itemA, itemB] }];
        cp.reindex();
        expect(cp.isSelected(itemA)).toBe(true);
        expect(cp.isSelected(itemB)).toBe(false);
    });
});

describe('goto', () => {
    it('navigates to the item url', () => {
        const cp = makePalette();
        const setter = stubLocationHref();
        cp.goto({ url: '/members/1' });
        expect(setter).toHaveBeenCalledWith('/members/1');
    });

    it('does nothing for an item without a url', () => {
        const cp = makePalette();
        const setter = stubLocationHref();
        cp.goto({ name: 'x' });
        cp.goto(null);
        expect(setter).not.toHaveBeenCalled();
    });
});

describe('handleKey', () => {
    it('moves the selection down and up within bounds', () => {
        const cp = makePalette();
        cp.groups = [{ items: [{ name: 'a' }, { name: 'b' }, { name: 'c' }] }];
        cp.reindex();
        const preventDefault = vi.fn();
        cp.handleKey({ key: 'ArrowDown', preventDefault });
        expect(cp.selected).toBe(1);
        cp.handleKey({ key: 'ArrowDown', preventDefault });
        cp.handleKey({ key: 'ArrowDown', preventDefault });
        expect(cp.selected).toBe(2);
        cp.handleKey({ key: 'ArrowDown', preventDefault });
        expect(cp.selected).toBe(2);
        cp.handleKey({ key: 'ArrowUp', preventDefault });
        expect(cp.selected).toBe(1);
        cp.handleKey({ key: 'ArrowUp', preventDefault });
        cp.handleKey({ key: 'ArrowUp', preventDefault });
        expect(cp.selected).toBe(0);
        cp.handleKey({ key: 'ArrowUp', preventDefault });
        expect(cp.selected).toBe(0);
    });

    it('navigates to the selected item on Enter', () => {
        const cp = makePalette();
        const setter = stubLocationHref();
        cp.groups = [{ items: [{ name: 'a', url: '/members/2' }] }];
        cp.reindex();
        cp.handleKey({ key: 'Enter', preventDefault: vi.fn() });
        expect(setter).toHaveBeenCalledWith('/members/2');
    });

    it('closes the palette on Escape', () => {
        const cp = makePalette();
        cp.open = true;
        cp.handleKey({ key: 'Escape' });
        expect(cp.open).toBe(false);
    });
});

describe('handleGlobalKey', () => {
    it('opens the palette on /', () => {
        const cp = makePalette();
        const event = { key: '/', preventDefault: vi.fn(), target: document.body };
        cp.handleGlobalKey(event);
        expect(cp.open).toBe(true);
        expect(event.preventDefault).toHaveBeenCalled();
    });

    it('ignores / while the palette is already open', () => {
        const cp = makePalette();
        cp.open = true;
        const event = { key: '/', preventDefault: vi.fn(), target: document.body };
        cp.handleGlobalKey(event);
        expect(event.preventDefault).not.toHaveBeenCalled();
    });

    it('ignores shortcuts while typing in an input', () => {
        const cp = makePalette();
        const input = document.createElement('input');
        document.body.appendChild(input);
        const event = { key: '/', preventDefault: vi.fn(), target: input };
        cp.handleGlobalKey(event);
        expect(cp.open).toBe(false);
        expect(event.preventDefault).not.toHaveBeenCalled();
    });

    it('navigates to the new-member form on n', () => {
        const cp = makePalette({ newMemberUrl: '/members/create' });
        const setter = stubLocationHref();
        cp.handleGlobalKey({ key: 'n', shiftKey: false, target: document.body });
        expect(setter).toHaveBeenCalledWith('/members/create');
    });

    it('ignores n when shift is held', () => {
        const cp = makePalette({ newMemberUrl: '/members/create' });
        const setter = stubLocationHref();
        cp.handleGlobalKey({ key: 'n', shiftKey: true, target: document.body });
        expect(setter).not.toHaveBeenCalled();
    });

    it('ignores n when no new-member URL is configured', () => {
        const cp = makePalette();
        const setter = stubLocationHref();
        cp.handleGlobalKey({ key: 'n', shiftKey: false, target: document.body });
        expect(setter).not.toHaveBeenCalled();
    });

    it.each(['a', 'A'])('triggers the approve shortcut on %s', (key) => {
        const cp = makePalette();
        const form = shortcutEl('form', 'approve', true);
        const button = document.createElement('button');
        button.setAttribute('type', 'submit');
        form.appendChild(button);
        const click = vi.spyOn(button, 'click').mockImplementation(() => {});
        const event = { key, preventDefault: vi.fn(), target: document.body };
        cp.handleGlobalKey(event);
        expect(click).toHaveBeenCalledTimes(1);
        expect(event.preventDefault).toHaveBeenCalled();
    });

    it.each(['r', 'R'])('triggers the reject shortcut on %s', (key) => {
        const cp = makePalette();
        const form = shortcutEl('form', 'reject', true);
        const button = document.createElement('button');
        button.setAttribute('type', 'submit');
        form.appendChild(button);
        const click = vi.spyOn(button, 'click').mockImplementation(() => {});
        const event = { key, preventDefault: vi.fn(), target: document.body };
        cp.handleGlobalKey(event);
        expect(click).toHaveBeenCalledTimes(1);
        expect(event.preventDefault).toHaveBeenCalled();
    });

    it('does nothing when the approve shortcut is absent', () => {
        const cp = makePalette();
        const event = { key: 'a', target: document.body };
        expect(() => cp.handleGlobalKey(event)).not.toThrow();
    });
});
