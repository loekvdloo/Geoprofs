// resources/js/Pages/Dashboard.jsx
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function Dashboard() {
    return (
        <AuthenticatedLayout>
            <Head title="Dashboard" />
            <div className="p-6">
                <h1 className="text-2xl font-bold mb-4">Je bent ingelogd ✅</h1>
                <p>We hebben je loginpoging ook gelogd in de database.</p>
            </div>
        </AuthenticatedLayout>
    );
}
