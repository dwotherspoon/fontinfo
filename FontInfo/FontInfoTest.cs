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
            Assert.AreEqual(fi.Type, FontInfo.fontType.TTF);
            Assert.AreEqual(fi2.Type, FontInfo.fontType.OTF);
        }
    }
}
