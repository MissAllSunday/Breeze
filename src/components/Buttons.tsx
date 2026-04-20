import type React from "react";

interface ButtonsProps {
  children: React.ReactNode;
}

export default function Buttons({ children }: ButtonsProps) {
  return (
      <ul className="dropmenu breezeButtons">
        {children}
      </ul>
  );
}
