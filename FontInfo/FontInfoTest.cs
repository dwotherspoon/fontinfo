using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using NUnit.Framework;

namespace FontInfo
{
    [TestFixture]
    class FontInfoTest
    {
        [SetUp]
        public void Init()
        {
            //
        }

        [Test]
        public void Test1()
        {
            FontInfo fi = new FontInfo("Not a file");
            Assert.NotNull(fi);
        }

        [Test]
        public void TestTTF()
        {
            FontInfo fi = new FontInfo(@"C:\Windows\Fonts\times.ttf");
            FontInfo fi2 = new FontInfo(@"C:\Windows\Fonts\MyriadPro-Bold.otf");
            FontInfo fi3 = new FontInfo(@"C:\Windows\Fonts\Sproketf.PFB");
            FontInfo fi4 = new FontInfo(@"C:\Windows\Fonts\CAMBRIA.TTC");
            Assert.AreEqual(FontInfo.fontType.TTF, fi.Type);
            Assert.AreEqual(FontInfo.fontType.OTF, fi2.Type);
            Assert.AreEqual(FontInfo.fontType.PFB, fi3.Type);
            Assert.AreEqual(FontInfo.fontType.TTC, fi4.Type);
        }

        [Test]
        public void TestOTF()
        {
            //FontInfo fi = new FontInfo(@"C:\Windows\Fonts\MyriadPro-Bold.otf");
            FontInfo fi2 = new FontInfo(@"C:\Windows\Fonts\PoplarStd.otf");
            //fi.readInfo();
            fi2.readInfo();
            //Assert.AreEqual(FontInfo.fontType.OTF, fi.Type);
        }
    }
}
