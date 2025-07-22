import '@testing-library/jest-dom';
import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest'
import { render, screen } from '@testing-library/react';
import React from 'react';

import Tab from './Tab';

describe('Tab component', () => {
  it('renders HTML content correctly', () => {
    const htmlContent = '<p>Test content</p><span>More content</span>';
    render(<Tab content={htmlContent} name="Test Tab" />);

    // The component should render the HTML content
    const element = screen.getByText('Test content');
    expect(element).toBeInTheDocument();
    expect(screen.getByText('More content')).toBeInTheDocument();
  });

  it('renders complex HTML content', () => {
    const complexHtml = `
      <div class="test-class">
        <h2>Test Header</h2>
        <p>Paragraph with <strong>bold text</strong> and <em>italic text</em></p>
        <ul>
          <li>Item 1</li>
          <li>Item 2</li>
        </ul>
      </div>
    `;

    render(<Tab content={complexHtml} name="Complex Tab" />);

    expect(screen.getByText('Test Header')).toBeInTheDocument();
    expect(screen.getByText('Item 1')).toBeInTheDocument();
    expect(screen.getByText('Item 2')).toBeInTheDocument();

    // Check for the bold and italic text
    const boldText = screen.getByText('bold text');
    expect(boldText.tagName).toBe('STRONG');

    const italicText = screen.getByText('italic text');
    expect(italicText.tagName).toBe('EM');
  });
});
