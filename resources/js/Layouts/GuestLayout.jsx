import ApplicationLogo from "@/Components/ApplicationLogo";
import { Link } from "@inertiajs/react";

export default function GuestLayout({ children }) {
    return (
        <div className="flex min-h-screen flex-col items-center justify-center bg-[#F3F4F6] font-inter">
            <div className="flex flex-col items-center mb-6">
                <div className="flex items-center space-x-2">
                    <div className="bg-[#0E3A5B] text-white font-bold rounded-full w-10 h-10 flex items-center justify-center text-xl">
                        G
                    </div>
                    <h1 className="text-2xl font-semibold text-[#0E3A5B]">
                        GeoProfs
                    </h1>
                </div>
            </div>

            <div className="w-full max-w-md rounded-2xl bg-white p-8 shadow-lg relative overflow-hidden">
                <div
                    className="absolute bottom-0 left-0 right-0 h-24 bg-[#0E3A5B]/5 rounded-b-2xl"
                    style={{
                        backgroundImage: "url('/img/hoogtelijnen.svg')",
                        backgroundRepeat: "no-repeat",
                        backgroundSize: "cover",
                        opacity: 0.2,
                    }}
                />
                <div className="relative z-10">{children}</div>
            </div>
        </div>
    );
}
