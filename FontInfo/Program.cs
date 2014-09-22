using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Microsoft.Win32;
using System.IO;
using System.Runtime.InteropServices;


namespace FontInfo
{
    class Program
    {
        [DllImport("user32.dll")]
        public static extern int SendMessage(int hWnd, uint Msg, int wParam, int lParam);
        const int WM_FONTCHANGE = 0x001D;
        const int HWND_BROADCAST = 0xffff;

        [DllImport("gdi32.dll", EntryPoint = "AddFontResourceW", SetLastError = true)]
        public static extern int AddFontResource([In][MarshalAs(UnmanagedType.LPWStr)] string lpFileName);

        static void Main(string[] args)
        {

            FontInfo fi = new FontInfo(@"C:\Users\Christine\Desktop\Gotham-Book.otf");
            fi.readInfo();

            /*
            RegistryKey rkey = Registry.LocalMachine.OpenSubKey(@"SOFTWARE\Microsoft\Windows NT\CurrentVersion\Fonts", true);
            //File.Copy(@"C:\test.ttf", @"C:\Windows\Fonts\test.ttf");
            string[] test = rkey.GetValueNames();// OpenSubKey(@"HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows NT\Current Version\Fonts").GetValueNames();
            foreach (string t in test)
            {
                Console.WriteLine(t);
            }
            rkey.SetValue("Open Sans Semibold (TrueType)", @"C:\MyFonts\OpenSans-Semibold.ttf", RegistryValueKind.String);
            rkey.Close();
            Console.WriteLine(AddFontResource(@"C:\MyFonts\OpenSans-Semibold.ttf"));
            SendMessage(HWND_BROADCAST, WM_FONTCHANGE, 0, 0);*/
            Console.ReadLine();
        }
    }
}
