import { useEffect, useState } from "react";
import { useForm, usePage } from "@inertiajs/react";

export default function AuthenticatedLayout({ children }) {
    const { auth, url } = usePage().props;
    const { post, processing } = useForm({});
    const [loading, setLoading] = useState(true);
    const [userName, setUserName] = useState("");

    const isAdmin = auth?.user && Number(auth.user.role_id) === 1;
    const isManager = auth?.user && Number(auth.user.role_id) === 3;

    useEffect(() => {
        const name =
            auth?.user?.name ??
            [auth?.user?.voornaam, auth?.user?.achternaam]
                .filter(Boolean)
                .join(" ") ??
            "";
        setUserName(name);
        setLoading(false);
    }, [auth]);

    const handleLogout = (e) => {
        e.preventDefault();
        post(route("logout"));
    };

    // Kleine helper voor nette nav styling + active state
    const isActive = (href) => {
        // Inertia geeft vaak url zoals "/verlof" of "/verlof/overzicht"
        const current = typeof url === "string" ? url : window.location.pathname;
        return current === href || current.startsWith(href + "/");
    };

    const NavLink = ({ href, children }) => (
        <a
            href={href}
            className={`px-3 py-2 rounded-md text-sm font-medium transition ${
                isActive(href)
                    ? "bg-white/15 text-white"
                    : "text-white/90 hover:text-white hover:bg-white/10"
            }`}
        >
            {children}
        </a>
    );

    if (loading) {
        return (
            <div className="flex items-center justify-center h-screen bg-[#F3F4F6]">
                <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-[#0E3A5B]" />
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-[#F3F4F6]">
            <header className="bg-[#0E3A5B] text-white shadow">
                <div className="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
                    {/* Brand */}
                    <a href="/dashboard" className="flex items-center gap-2">
                        <h1 className="text-lg sm:text-xl font-bold tracking-wide">
                            GeoProfs
                        </h1>
                    </a>

                    {/* Nav */}
                    <nav className="hidden sm:flex items-center gap-1">
                        <NavLink href="/dashboard">Dashboard</NavLink>

                        <NavLink href="/verlof">Verlof</NavLink>

                        {(isAdmin || isManager) && (
                            <NavLink href="/verlof/overzicht">Overzicht</NavLink>
                        )}

                        {(isAdmin || isManager) && (
                            <NavLink href="/verlof/bezetting">Bezetting</NavLink>
                        )}

                        {isAdmin && <NavLink href="/admin/users">Gebruikers</NavLink>}
                        {isAdmin && <NavLink href="/records">Records</NavLink>}
                    </nav>

                    {/* Right side */}
                    <div className="flex items-center gap-3">
                        <a
                            href="/profile/edit"
                            className="px-3 py-2 rounded-md text-sm font-medium text-white/90 hover:text-white hover:bg-white/10 transition"
                        >
                            {userName || "Profiel"}
                        </a>

                        <span className="hidden sm:inline opacity-30">|</span>

                        <button
                            onClick={handleLogout}
                            className="px-3 py-2 rounded-md text-sm font-medium text-white/90 hover:text-white hover:bg-white/10 transition disabled:opacity-50"
                            disabled={processing}
                            type="button"
                        >
                            Logout
                        </button>
                    </div>
                </div>

                {/* Mobile nav (compact) */}
                <div className="sm:hidden border-t border-white/10 px-4 py-2 flex flex-wrap gap-2">
                    <NavLink href="/dashboard">Dashboard</NavLink>
                    <NavLink href="/verlof">Verlof</NavLink>

                    {(isAdmin || isManager) && (
                        <NavLink href="/verlof/overzicht">Overzicht</NavLink>
                    )}
                    {(isAdmin || isManager) && (
                        <NavLink href="/verlof/bezetting">Bezetting</NavLink>
                    )}

                    {isAdmin && <NavLink href="/admin/users">Gebruikers</NavLink>}
                    {isAdmin && <NavLink href="/records">Records</NavLink>}
                </div>
            </header>

            <main className="max-w-7xl mx-auto p-6">{children}</main>
        </div>
    );
}
