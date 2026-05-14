import type { ImgHTMLAttributes } from 'react';

export default function AppLogoIcon(props: ImgHTMLAttributes<HTMLImageElement>) {
    return (
        <img
            src="/sadiq-favicon.svg"
            alt="Sadiq Auto Parts"
            {...props}
        />
    );
}
