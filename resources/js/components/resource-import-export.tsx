import { router } from '@inertiajs/react';
import { Download, Upload } from 'lucide-react';
import { useRef, useState } from 'react';

interface Props {
    exportUrl: string;
    importUrl: string;
    entityName: string;
}

export default function ResourceImportExport({ exportUrl, importUrl, entityName }: Props) {
    const fileInputRef = useRef<HTMLInputElement>(null);
    const [importing, setImporting] = useState(false);

    function handleFileChange(event: React.ChangeEvent<HTMLInputElement>) {
        const file = event.target.files?.[0];

        if (!file) {
            return;
        }

        const formData = new FormData();
        formData.append('file', file);

        setImporting(true);

        router.post(importUrl, formData, {
            forceFormData: true,
            preserveScroll: true,
            onFinish: () => {
                setImporting(false);
                event.target.value = '';
            },
        });
    }

    return (
        <div className="flex flex-wrap items-center gap-2">
            <a
                href={exportUrl}
                className="inline-flex items-center gap-2 rounded-lg border border-input bg-background px-3 py-2 text-sm font-semibold text-foreground transition-colors hover:bg-muted"
            >
                <Download className="h-4 w-4" />
                Export CSV
            </a>
            <button
                type="button"
                onClick={() => fileInputRef.current?.click()}
                disabled={importing}
                className="inline-flex items-center gap-2 rounded-lg bg-sadiq-teal px-3 py-2 text-sm font-semibold text-white transition-colors hover:bg-sadiq-teal/90 disabled:cursor-not-allowed disabled:opacity-60"
            >
                <Upload className="h-4 w-4" />
                {importing ? `Importing ${entityName}...` : 'Import CSV'}
            </button>
            <input
                id={`csv-import-${entityName.toLowerCase()}`}
                name="file"
                aria-label={`Import ${entityName} CSV File`}
                ref={fileInputRef}
                type="file"
                accept=".csv,text/csv"
                className="hidden"
                onChange={handleFileChange}
            />
        </div>
    );
}