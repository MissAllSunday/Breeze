import type React from "react";

interface ButtonProps {
  label: string;
  icon?: string;
  onClick: () => void;
  className?: string;
}

export default function Button({ label, onClick, className = "" }: ButtonProps): React.JSX.Element {
  return (
    <a
      href="#"
      onClick={(e) => {
        e.preventDefault();
        onClick();
      }}
      className={`button-link ${className}`}
      style={{ cursor: 'pointer' }}
    >
      <span className="text">{label}</span>
    </a>
  );
}
