import { useEffect, useRef, useState } from 'react';

import smfVars from '../DataSource/SMF';

export const SMFEditorEvent = (callback: () => void) => {
  const ref = useRef<HTMLDivElement>(null);
  const [isAlreadyClicked, setIsAlreadyClicked] = useState(false);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (ref.current && !ref.current.contains(event.target as Node) && (event.target as HTMLElement).id === 'smfEditor') {
        if (isAlreadyClicked) {
          event.preventDefault();

          return;
        }

        setIsAlreadyClicked(true);
        callback();
      }
    };

    document.addEventListener('click', handleClickOutside);

    return () => {
      document.removeEventListener('click', handleClickOutside);
    };
  }, [callback]);

  return ref;
};
