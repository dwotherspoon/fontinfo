using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.IO;
using System.Diagnostics;

namespace FontInfo
{
    public class FontInfo
    {
        public enum fontType { TTF, OTF, FON, TTC, PFB, PFA, ERR};
        private fontType type = fontType.ERR;
        public fontType Type
        {
            get { return type; }
        }

        private BinaryReader reader;

        public FontInfo(string file)
        {
            if (!File.Exists(file)) throw new FileNotFoundException();
            this.reader = new BinaryReader(new FileStream(file, FileMode.Open, FileAccess.Read));
            byte[] magic_num = reader.ReadBytes(4);
            Console.WriteLine(magic_num[0] + ", " + magic_num[1] + ", " + magic_num[2] + ", " + magic_num[3]);
            if (magic_num.Intersect(new byte[] {0x00, 0x01, 0x00, 0x00}).Count() == 4)
            {
                type = fontType.TTF;
            }
            else if (magic_num.Intersect(new byte[] {0x4F, 0x54, 0x54, 0x4F}).Count() == 4)
            {
                type = fontType.OTF;
            }
        }


    }
}
