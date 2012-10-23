#include "cinder/app/AppBasic.h"
#include "License.h"

using namespace std;
using namespace mndl::license;
using namespace ci;
using namespace ci::app;

int main(int argc, char *argv[])
{
	License license;

//	fs::path xmlData( getAssetPath( "license.xml" ));
//	license.init( xmlData );

	license.setKey( "public.pem" );
	license.setProduct( "TouchMovie" );
	license.addServer( "http://www.mndl.hu/lcnc/licensehandler.php" );

	if( license.process())
		cout << "success";
	else
		cout << "failed";
}
