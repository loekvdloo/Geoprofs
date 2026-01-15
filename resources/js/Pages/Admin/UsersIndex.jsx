import React from "react";
import { Link } from "@inertiajs/react";

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

export default function UsersIndex({ auth, users }) {
    return (
        <AuthenticatedLayout>
            <div className="max-w-5xl mx-auto p-6 space-y-6">
                <h1
                    data-testid="users-index-title"
                    className="text-2xl font-bold mb-4"
                >
                    Gebruikerslijst
                </h1>

                {users.length === 0 ? (
                    <p data-testid="no-users" className="text-gray-600">
                        Geen gebruikers gevonden.
                    </p>
                ) : (
                    <div className="overflow-x-auto bg-white shadow rounded-lg">
                        <table
                            className="min-w-full divide-y divide-gray-200"
                            data-testid="users-table"
                        >
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">
                                        Naam
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">
                                        Email
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">
                                        Rol
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">
                                        Afdeling
                                    </th>
                                    <th className="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">
                                        Acties
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {users.map((user) => (
                                    <tr
                                        key={user.user_id}
                                        data-testid={`user-row-${user.user_id}`}
                                    >
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            {user.voornaam} {user.achternaam}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            {user.email}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            {user.role?.role_naam || "-"}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            {user.afdeling?.afdeling_naam ||
                                                "-"}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <Link
                                                href={`/admin/users/${user.user_id}/edit`}
                                                data-testid={`edit-user-${user.user_id}`}
                                                className="text-blue-600 hover:underline"
                                            >
                                                Bewerken
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
