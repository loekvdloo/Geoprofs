import React, { useState } from "react";
import axios from "axios";
import { Inertia } from "@inertiajs/inertia"; // voor navigatie
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

export default function UserRoleEdit({ auth, user, roles, afdelingen }) {
    const [roleId, setRoleId] = useState(user.role_id || "");
    const [afdelingId, setAfdelingId] = useState(user.afdeling_id || "");
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState(null);
    const [error, setError] = useState(null);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setMessage(null);
        setError(null);

        try {
            const response = await axios.put(
                `/api/admin/users/${user.user_id}/role-afdeling`,
                {
                    role_id: parseInt(roleId),
                    afdeling_id: parseInt(afdelingId),
                },
                {
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                    },
                }
            );

            setMessage(
                response.data.message || "Rol en afdeling succesvol opgeslagen"
            );
        } catch (err) {
            console.error(err);
            setError(err.response?.data?.message || "Opslaan mislukt");
        } finally {
            setLoading(false);
        }
    };

    const handleBack = () => {
        // Navigeren naar de gebruikerslijst
        Inertia.visit("/admin/users");
    };

    return (
        <AuthenticatedLayout auth={auth}>
            <div className="max-w-3xl mx-auto mt-8 p-6 bg-white rounded-xl shadow-md">
                <h1 className="text-2xl font-bold mb-6">
                    Gebruiker beheren: {user.voornaam} {user.achternaam}
                </h1>

                <form onSubmit={handleSubmit} data-testid="user-role-form">
                    {/* Rol */}
                    <div className="mb-5">
                        <label
                            htmlFor="role"
                            className="block text-gray-700 font-semibold mb-2"
                        >
                            Rol
                        </label>
                        <select
                            id="role"
                            name="role_id"
                            value={roleId}
                            onChange={(e) => setRoleId(e.target.value)}
                            className="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            data-testid="role-select"
                        >
                            {roles.map((role) => (
                                <option key={role.role_id} value={role.role_id}>
                                    {role.role_naam}
                                </option>
                            ))}
                        </select>
                    </div>

                    {/* Afdeling */}
                    <div className="mb-5">
                        <label
                            htmlFor="afdeling"
                            className="block text-gray-700 font-semibold mb-2"
                        >
                            Afdeling
                        </label>
                        <select
                            id="afdeling"
                            name="afdeling_id"
                            value={afdelingId}
                            onChange={(e) => setAfdelingId(e.target.value)}
                            className="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            data-testid="afdeling-select"
                        >
                            {afdelingen.map((afd) => (
                                <option
                                    key={afd.afdeling_id}
                                    value={afd.afdeling_id}
                                >
                                    {afd.afdeling_naam}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="flex gap-4">
                        {/* Opslaan knop */}
                        <button
                            type="submit"
                            disabled={loading}
                            className="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-3 rounded-lg shadow transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            data-testid="save-button"
                        >
                            {loading ? "Opslaan..." : "Opslaan"}
                        </button>

                        {/* Terug knop */}
                        <button
                            type="button"
                            onClick={handleBack}
                            className="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold px-4 py-3 rounded-lg shadow transition duration-200"
                            data-testid="back-button"
                        >
                            Terug
                        </button>
                    </div>

                    {/* Feedback messages */}
                    {message && (
                        <p
                            className="mt-4 text-green-600 font-medium"
                            data-testid="success-message"
                        >
                            {message}
                        </p>
                    )}
                    {error && (
                        <p
                            className="mt-4 text-red-600 font-medium"
                            data-testid="error-message"
                        >
                            {error}
                        </p>
                    )}
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
