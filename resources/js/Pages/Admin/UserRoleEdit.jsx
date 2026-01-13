import React, { useState } from "react";
import axios from "axios";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout"; // als je AuthenticatedLayout gebruikt

export default function UserRoleEdit({ user, roles, afdelingen, auth }) {
    // State voor geselecteerde rol & afdeling
    const [roleId, setRoleId] = useState(parseInt(user.role_id));
    const [afdelingId, setAfdelingId] = useState(parseInt(user.afdeling_id));
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState(null);
    const [error, setError] = useState(null);

    const save = async () => {
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
            if (err.response?.data?.message) {
                setError(err.response.data.message);
            } else {
                setError("Opslaan mislukt");
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <AuthenticatedLayout auth={auth}>
            <div className="max-w-3xl mx-auto mt-8 p-6 bg-white rounded-xl shadow-md">
                <h1 className="text-2xl font-bold mb-6 text-gray-800">
                    Gebruiker beheren: {user.voornaam} {user.achternaam}
                </h1>

                {/* Rol */}
                <div className="mb-5">
                    <label className="block text-gray-700 font-semibold mb-2">
                        Rol
                    </label>
                    <select
                        value={roleId}
                        onChange={(e) => setRoleId(parseInt(e.target.value))}
                        className="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
                    <label className="block text-gray-700 font-semibold mb-2">
                        Afdeling
                    </label>
                    <select
                        value={afdelingId}
                        onChange={(e) =>
                            setAfdelingId(parseInt(e.target.value))
                        }
                        className="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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

                {/* Opslaan knop */}
                <button
                    onClick={save}
                    disabled={loading}
                    className="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-3 rounded-lg shadow transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {loading ? "Opslaan..." : "Opslaan"}
                </button>

                {/* Feedback messages */}
                {message && (
                    <p className="mt-4 text-green-600 font-medium">{message}</p>
                )}
                {error && (
                    <p className="mt-4 text-red-600 font-medium">{error}</p>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
