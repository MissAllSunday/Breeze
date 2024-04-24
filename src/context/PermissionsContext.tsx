import { PermissionsContextType } from 'breezeTypes';
import { createContext } from 'react';

export const PermissionsContext = createContext<PermissionsContextType>(null);
