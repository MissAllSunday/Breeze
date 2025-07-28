import type { ReactElement } from 'react';

export default function Loading(): ReactElement {
  return (
    <div className="loading" data-testid="loading">&#8230;</div>
  );
}
