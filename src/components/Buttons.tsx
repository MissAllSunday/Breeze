import type React from "react";

interface ButtonsProps {
  children: React.ReactNode;
}

export default function Buttons({ children }: ButtonsProps) {
  return (
    <div id="Breeze_buttons" className="generic_menu">
      <ul className="dropmenu breezeButtons">
        {children}
      </ul>
    </div>
  );
}
