import '@testing-library/jest-dom';

import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import React from 'react';

import Tabs from './Tabs';

// Mock the text variables
jest.mock('../DataSource/Txt', () => ({
  tabs: {
    wall: 'Wall',
    about: 'About',
    activity: 'Activity',
  },
}));

describe('Tabs component', () => {
  const renderTabs = () => {
    return render(
      <Tabs>
        <div data-testid="tab-content-0">Wall Content</div>
        <div data-testid="tab-content-1">About Content</div>
        <div data-testid="tab-content-2">Activity Content</div>
      </Tabs>,
    );
  };

  it('renders tabs with correct names', () => {
    renderTabs();

    expect(screen.getByText('Wall')).toBeInTheDocument();
    expect(screen.getByText('About')).toBeInTheDocument();
    expect(screen.getByText('Activity')).toBeInTheDocument();
  });

  it('sets the first tab as active by default', () => {
    renderTabs();

    const firstTabLink = screen.getByText('Wall');
    expect(firstTabLink).toHaveClass('active');

    // First tab content should be visible
    const firstTabContent = screen.getByTestId('tab-content-0');
    expect(firstTabContent.parentElement).toHaveClass('show');
  });

  it('changes active tab when clicked', async () => {
    renderTabs();

    // Click on the second tab
    const secondTabLink = screen.getByText('About');
    await userEvent.click(secondTabLink);

    // Second tab should now be active
    await waitFor(() => {
      expect(secondTabLink).toHaveClass('active');

      // First tab should no longer be active
      const firstTabLink = screen.getByText('Wall');
      expect(firstTabLink).not.toHaveClass('active');

      // Second tab content should be visible
      const secondTabContent = screen.getByTestId('tab-content-1');
      expect(secondTabContent.parentElement).toHaveClass('show');

      // First tab content should be hidden
      const firstTabContent = screen.getByTestId('tab-content-0');
      expect(firstTabContent.parentElement).toHaveClass('hide');
    });
  });

  it('does nothing when clicking the already active tab', async () => {
    renderTabs();

    // First tab is active by default
    const firstTabLink = screen.getByText('Wall');
    expect(firstTabLink).toHaveClass('active');

    // Click on the first tab again
    await userEvent.click(firstTabLink);

    // First tab should still be active
    await waitFor(() => {
      expect(firstTabLink).toHaveClass('active');

      // First tab content should still be visible
      const firstTabContent = screen.getByTestId('tab-content-0');
      expect(firstTabContent.parentElement).toHaveClass('show');
    });
  });

  it('renders the correct number of tabs', () => {
    renderTabs();

    const tabLinks = screen.getAllByRole('link');
    expect(tabLinks).toHaveLength(3);
  });
});
