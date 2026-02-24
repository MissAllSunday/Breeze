import '@testing-library/jest-dom/vitest';

// Mock scrollIntoView which is not available in jsdom
Element.prototype.scrollIntoView = () => {};
