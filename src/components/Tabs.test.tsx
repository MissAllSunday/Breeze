import '@testing-library/jest-dom';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it } from 'vitest';

import Tab from './Tab';
import Tabs from './Tabs';

describe('Tabs component', () => {
  const renderTabs = () => {
    return render(
      <Tabs>
        <Tab name="Tab 1" content="<p>Content 1</p>" />
        <Tab name="Tab 2" content="<p>Content 2</p>" />
        <Tab name="Tab 3" content="<p>Content 3</p>" />
      </Tabs>,
    );
  };

  it('renders tabs with correct names', () => {
    renderTabs();

    expect(screen.getByText('Tab 1')).toBeInTheDocument();
    expect(screen.getByText('Tab 2')).toBeInTheDocument();
    expect(screen.getByText('Tab 3')).toBeInTheDocument();
  });

  it('sets the first tab as active by default', () => {
    renderTabs();

    const firstTabLink = screen.getByText('Tab 1');
    expect(firstTabLink).toHaveClass('active');

    // First tab content should be visible
    const tabContents = screen.getAllByRole('listitem');
    expect(tabContents[3]).toHaveClass('show'); // First tab content (index 3 because there are 3 tab links first)
  });

  it('changes active tab when clicked', async () => {
    renderTabs();

    // Click on the second tab
    const secondTabLink = screen.getByText('Tab 2');
    await userEvent.click(secondTabLink);

    // Second tab should now be active
    await waitFor(() => {
      expect(secondTabLink).toHaveClass('active');

      // First tab should no longer be active
      const firstTabLink = screen.getByText('Tab 1');
      expect(firstTabLink).not.toHaveClass('active');

      // Check content visibility
      const tabContents = screen.getAllByRole('listitem');
      expect(tabContents[3]).toHaveClass('hide'); // First tab content
      expect(tabContents[4]).toHaveClass('show'); // Second tab content
    });
  });

  it('does nothing when clicking the already active tab', async () => {
    renderTabs();

    // First tab is active by default
    const firstTabLink = screen.getByText('Tab 1');
    expect(firstTabLink).toHaveClass('active');

    // Click on the first tab again
    await userEvent.click(firstTabLink);

    // First tab should still be active
    await waitFor(() => {
      expect(firstTabLink).toHaveClass('active');

      // First tab content should still be visible
      const tabContents = screen.getAllByRole('listitem');
      expect(tabContents[3]).toHaveClass('show');
    });
  });

  it('renders the correct number of tabs', () => {
    renderTabs();

    const tabLinks = screen.getAllByRole('link');
    expect(tabLinks).toHaveLength(3);
  });
});
