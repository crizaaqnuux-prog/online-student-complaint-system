import React, { useState } from 'react';
import { GraduationCap, Menu, X, ShieldCheck, ChevronRight, Trash2, Pencil, Camera, LayoutDashboard, LogOut } from 'lucide-react';

const OnlineStudentComplaintSystem = () => {
  // State for dynamic homepage images
  const [aboutImage, setAboutImage] = useState("https://images.unsplash.com/photo-1541339907198-e08756ebafe3?auto=format&fit=crop&q=80&w=800");
  const [heroImage, setHeroImage] = useState("https://images.unsplash.com/photo-1522071820081-009f0129c71c");

  // State for view toggle (simulate admin vs home)
  const [isAdmin, setIsAdmin] = useState(false);
  const [isMenuOpen, setIsMenuOpen] = useState(false);

  // Placeholder for when an image is deleted
  const placeholderImage = "https://via.placeholder.com/800x600/6C63FF/FFFFFF?text=Broken+Image+-+Upload+New";

  // Admin Management Logic
  const handleUrlUpdate = (setter) => {
    const newUrl = prompt("Enter the new Image URL:");
    if (newUrl) setter(newUrl);
  };

  const handleDelete = (setter) => {
    if (confirm("Are you sure you want to remove this image?")) {
      setter(placeholderImage);
    }
  };

  return (
    <div className="min-h-screen bg-white font-sans text-gray-900">
      {/* Dynamic Navbar */}
      <nav className="bg-[#6C63FF] text-white sticky top-0 z-50 shadow-lg">
        <div className="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center h-20">
            {/* Logo */}
            <div className="flex items-center gap-2 group cursor-pointer" onClick={() => setIsAdmin(false)}>
              <div className="bg-white p-2 rounded-lg text-[#6C63FF] transition-transform group-hover:rotate-12">
                <GraduationCap size={28} />
              </div>
              <span className="text-xl font-bold tracking-tight lowercase hidden sm:inline">
                online student complaint system
              </span>
            </div>

            {/* Desktop Navigation */}
            <div className="hidden md:flex items-center space-x-6">
              {!isAdmin ? (
                <>
                  <a href="#" className="hover:text-[#FFD700] transition-colors font-medium">Home</a>
                  <a href="#" className="hover:text-[#FFD700] transition-colors font-medium">Features</a>
                  <a href="#" className="hover:text-[#FFD700] transition-colors font-medium">About</a>
                  <a href="#" className="hover:text-[#FFD700] transition-colors font-medium">Contact</a>
                  <button
                    onClick={() => setIsAdmin(true)}
                    className="flex items-center gap-2 px-4 py-2 bg-[#FFD700] text-[#333] rounded-full font-bold hover:bg-white hover:text-[#6C63FF] transition-all"
                  >
                    <LayoutDashboard size={18} /> Admin Panel
                  </button>
                </>
              ) : (
                <>
                  <span className="font-bold text-[#FFD700]">ADMIN MODE activated</span>
                  <button
                    onClick={() => setIsAdmin(false)}
                    className="flex items-center gap-2 px-4 py-2 bg-white/10 border border-white/20 rounded-full hover:bg-white hover:text-[#6C63FF] transition-all"
                  >
                    <LogOut size={18} /> Exit Admin
                  </button>
                </>
              )}
            </div>

            {/* Mobile Menu Button */}
            <div className="md:hidden">
              <button
                onClick={() => setIsMenuOpen(!isMenuOpen)}
                className="p-2 hover:bg-white/10 rounded-lg transition-colors"
              >
                {isMenuOpen ? <X size={28} /> : <Menu size={28} />}
              </button>
            </div>
          </div>
        </div>
      </nav>

      {/* HERO SECTION */}
      <section className="relative pt-12 pb-20 lg:pt-24 lg:pb-32 overflow-hidden bg-white">
        <div className="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
            <div className="space-y-8 text-center lg:text-left">
              <div className="inline-block px-4 py-1.5 bg-[#6C63FF]/10 text-[#6C63FF] rounded-full text-sm font-bold tracking-wider uppercase">
                Bridging Communication Gaps
              </div>
              <h1 className="text-4xl sm:text-5xl lg:text-7xl font-black text-gray-900 leading-[1.1]">
                Transforming <span className="text-[#6C63FF]">Educational</span> Institutions
              </h1>
              <div className="space-y-6 max-w-2xl mx-auto lg:mx-0">
                <p className="text-lg sm:text-xl text-gray-600 leading-relaxed">
                  The Online Student Complaint Management System ensures transparency and accountability by digitalizing the resolution process.
                </p>
              </div>
              <div className="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start pt-4">
                <button className="flex items-center justify-center gap-2 px-8 py-4 bg-[#6C63FF] text-white rounded-2xl font-bold text-lg shadow-xl shadow-[#6C63FF]/30 hover:scale-105 transition-all">
                  Get Started <ChevronRight />
                </button>
              </div>
            </div>

            {/* Dynamic Hero Image */}
            <div className="relative group mx-auto lg:mx-0 w-full">
              <div className="absolute -inset-4 bg-[#FFD700]/30 rounded-[2.5rem] blur-3xl opacity-50 group-hover:opacity-75 transition-opacity"></div>
              <div className="relative z-10 rounded-[2rem] overflow-hidden shadow-2xl border-8 border-white">
                <img
                  src={heroImage}
                  alt="Students Collaborating"
                  className="w-full h-full object-cover aspect-[4/3] group-hover:scale-105 transition-transform duration-700"
                />
                <div className="absolute bottom-6 right-6 px-4 py-2 bg-white text-[#6C63FF] font-black text-sm rounded-xl shadow-2xl flex items-center gap-2 border border-blue-50">
                  <div className="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                  100% Secure
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ABOUT US SECTION WITH ADMIN FEATURES */}
      <section className="bg-gray-50 py-24 border-t border-gray-100">
        <div className="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid lg:grid-cols-2 gap-16 items-center">

            {/* IMAGE SECTION (LEFT SIDE) */}
            <div className="relative group">
              <div className="absolute -top-10 -left-10 w-40 h-40 bg-[#6C63FF]/10 rounded-full blur-2xl"></div>
              <div className="relative z-10 rounded-[3rem] overflow-hidden shadow-2xl shadow-[#6C63FF]/20 border-4 border-white">
                <img
                  src={aboutImage}
                  alt="about us"
                  className="w-full h-[500px] object-cover transition-all duration-700 group-hover:scale-110"
                />

                {/* ADMIN OVERLAY BUTTONS */}
                {isAdmin && (
                  <div className="absolute inset-0 bg-black/40 backdrop-blur-[2px] flex items-center justify-center gap-4 animate-in fade-in duration-300">
                    <button
                      onClick={() => handleUrlUpdate(setAboutImage)}
                      className="p-4 bg-[#FFD700] text-[#333] rounded-2xl shadow-xl hover:scale-110 transition-transform flex flex-col items-center gap-2 group/btn"
                      title="Upload/Add"
                    >
                      <Camera size={24} />
                      <span className="text-[10px] font-black uppercase">Upload</span>
                    </button>
                    <button
                      onClick={() => handleUrlUpdate(setAboutImage)}
                      className="p-4 bg-[#6C63FF] text-white rounded-2xl shadow-xl hover:scale-110 transition-transform flex flex-col items-center gap-2"
                      title="Edit"
                    >
                      <Pencil size={24} />
                      <span className="text-[10px] font-black uppercase">Edit</span>
                    </button>
                    <button
                      onClick={() => handleDelete(setAboutImage)}
                      className="p-4 bg-red-500 text-white rounded-2xl shadow-xl hover:scale-110 transition-transform flex flex-col items-center gap-2"
                      title="Delete"
                    >
                      <Trash2 size={24} />
                      <span className="text-[10px] font-black uppercase">Delete</span>
                    </button>
                  </div>
                )}

                <div className="absolute inset-0 bg-gradient-to-t from-[#6C63FF]/40 to-transparent pointer-events-none"></div>
                <div className="absolute bottom-10 left-10 p-6 bg-white/90 backdrop-blur-md rounded-2xl shadow-xl border border-white/50">
                  <div className="text-[#6C63FF] font-black text-3xl mb-1">98%</div>
                  <div className="text-gray-600 font-bold uppercase tracking-tighter text-xs">Student Satisfaction Rate</div>
                </div>
              </div>
              <div className="absolute -bottom-6 -right-6 w-32 h-32 bg-[#FFD700] rounded-full opacity-30 blur-2xl -z-0"></div>
            </div>

            {/* CONTENT SECTION (RIGHT SIDE) */}
            <div className="space-y-8">
              <div className="inline-block px-4 py-1 bg-[#FFD700]/20 text-[#333] rounded-lg text-sm font-bold uppercase tracking-widest">
                Horn of Africa University
              </div>
              <h2 className="text-4xl lg:text-6xl font-black text-gray-900 leading-tight">
                Empowering Your <span className="text-[#6C63FF]">Voice</span> Through Digital Tracking.
              </h2>
              <div className="space-y-6 text-lg text-gray-600 leading-relaxed">
                <p>
                  The Online Student Complaint Management System ensures transparency and accountability by digitalizing the grievance resolution process.
                </p>
                <p>
                  Our platform bridges the gap between students and university administration, ensuring every voice is heard and resolved efficiently.
                </p>
              </div>
              <div className="grid grid-cols-2 gap-6 pt-4">
                <div className="p-6 bg-white rounded-3xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                  <ShieldCheck className="text-[#6C63FF] mb-3" size={40} />
                  <div className="font-black text-gray-900 text-xl">100% Secure</div>
                  <p className="text-sm text-gray-500 mt-1">Encrypted and safely stored on campus servers.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="bg-gray-900 text-white py-12">
        <div className="max-w-[1280px] mx-auto px-4 text-center">
          <div className="flex justify-center items-center gap-2 mb-6 opacity-80">
            <GraduationCap size={24} />
            <span className="font-bold tracking-tight lowercase">online student complaint system</span>
          </div>
          <p className="text-gray-500 text-sm">&copy; 2026 Horn of Africa University. All rights reserved.</p>
        </div>
      </footer>

      <style jsx>{`
        @keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }
        .animate-in { animation: fade-in 0.4s ease-out; }
      `}</style>
    </div>
  );
};

export default OnlineStudentComplaintSystem;
