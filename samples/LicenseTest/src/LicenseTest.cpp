#include "cinder/app/AppBasic.h"
#include "cinder/Xml.h"
#include "License.h"

using namespace std;
using namespace mndl::license;
using namespace ci;
using namespace ci::app;

int main(int argc, char *argv[])
{
	License license;

////	fs::path xmlData( getAssetPath( "license.xml" ));
// 	fs::path xmlData( "../assets/license.xml" );
// 
// 	XmlTree doc( loadFile( xmlData ));
// 	if( doc.hasChild( "License" ))
// 	{
// 		XmlTree xmlLicense = doc.getChild( "License" );
// 
// 		string product = xmlLicense.getAttributeValue<string>( "Product", ""  );
// 		string key     = xmlLicense.getAttributeValue<string>( "Key"    , ""  );
// 
// 		license.setProduct( product );
// //		license.setKey( getAssetPath( key ));
// 		license.setKey( "../assets/" + key );
// 
// 		for( XmlTree::Iter child = xmlLicense.begin(); child != xmlLicense.end(); ++child )
// 		{
// 			if( child->getTag() == "Server" )
// 			{
// 				string server = child->getAttributeValue<string>( "Name" );
// 				license.addServer( server );
// 			}
// 		}
// 	}

	license.setKey( "public.pem" );
	license.setProduct( "nibbal" );
	license.addServer( "http://www.mndl.hu/lcnc/licensehandler.php" );

	if( license.process())
		cout << "success";
	else
		cout << "failed";
}
