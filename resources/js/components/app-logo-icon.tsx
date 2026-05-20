import type { ImgHTMLAttributes } from 'react';

export default function AppLogoIcon(props: ImgHTMLAttributes<HTMLImageElement>) {
    return (
        <img
            src="/sabr-89-favicon.svg"
            alt="Sabr 89"
            {...props}
        />
    );
}

