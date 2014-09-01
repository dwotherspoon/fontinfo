using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace FontInfo
{
    class Program
    {
        static void Main(string[] args)
        {
            FontInfo fi = new FontInfo(@"C:\Windows\Fonts\MYRIADPRO-REGULAR.OTF");
            fi.readInfo();

            FontInfo fi2 = new FontInfo(@"C:\Windows\Fonts\times.ttf");
            fi2.readInfo();

            FontInfo fi3 = new FontInfo(@"C:\Windows\Fonts\amiri-regular.ttf");
            fi3.readInfo();

            Console.ReadLine();
        }
    }
}
