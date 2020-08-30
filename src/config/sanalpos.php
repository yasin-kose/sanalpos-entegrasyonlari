<?php

return [

    // Currencies
    'currencies'    => [
        'TRY'       => 949,
        'USD'       => 840,
        'EUR'       => 978,
        'GBP'       => 826,
        'JPY'       => 392,
        'RUB'       => 643,
    ],

    // Banks
    'banks'         => [
        'akbank'    => [
            'name'  => 'AKBANK T.A.S.',
            'class' => SanalPos\EstPos::class,
            'urls'  => [
                'production'    => 'https://www.sanalakpos.com/fim/api',
                'test'          => 'https://entegrasyon.asseco-see.com.tr/fim/api',
                'gateway'       => [
                    'production'    => 'https://www.sanalakpos.com/fim/est3Dgate',
                    'test'          => 'https://entegrasyon.asseco-see.com.tr/fim/est3Dgate'
                ]
            ]
        ],
        'ziraat'    => [
            'name'  => 'Ziraat Bankası',
            'class' => SanalPos\EstPos::class,
            'urls'  => [
                'production'    => 'https://sanalpos2.ziraatbank.com.tr/fim/api',
                'test'          => 'https://entegrasyon.asseco-see.com.tr/fim/api',
                'gateway'       => [
                    'production'    => 'https://sanalpos2.ziraatbank.com.tr/fim/est3dgate',
                    'test'          => 'https://entegrasyon.asseco-see.com.tr/fim/est3Dgate'
                ]
            ]
        ],
        'finansbank'    => [
            'name'  => 'QNB Finansbank',
            'class' => SanalPos\EstPos::class,
            'urls'  => [
                'production'    => 'https://www.fbwebpos.com/fim/api',
                'test'          => 'https://entegrasyon.asseco-see.com.tr/fim/api',
                'gateway'       => [
                    'production'    => 'https://www.fbwebpos.com/fim/est3dgate',
                    'test'          => 'https://entegrasyon.asseco-see.com.tr/fim/est3Dgate'
                ]
            ]
        ],
        'turkiyefinans'    => [
            'name'  => 'Türkiye Finans',
            'class' => SanalPos\EstPos::class,
            'urls'  => [
                'production'    => 'https://sanalpos.turkiyefinans.com.tr/fim/api',
                'test'          => 'https://entegrasyon.asseco-see.com.tr/fim/api',
                'gateway'       => [
                    'production'    => 'https://sanalpos.turkiyefinans.com.tr/fim/est3dgate',
                    'test'          => 'https://entegrasyon.asseco-see.com.tr/fim/est3Dgate'
                ]
            ]
        ],
        'anadolubank'    => [
            'name'  => 'Anadolubank',
            'class' => SanalPos\EstPos::class,
            'urls'  => [
                'production'    => 'https://anadolusanalpos.est.com.tr/fim/api',
                'test'          => 'https://entegrasyon.asseco-see.com.tr/fim/api',
                'gateway'       => [
                    'production'    => 'https://anadolusanalpos.est.com.tr/fim/est3dgate',
                    'test'          => 'https://entegrasyon.asseco-see.com.tr/fim/est3Dgate'
                ]
            ]
        ],
        'halkbank'    => [
            'name'  => 'Halkbank',
            'class' => SanalPos\EstPos::class,
            'urls'  => [
                'production'    => 'https://sanalpos.halkbank.com.tr/fim/api',
                'test'          => 'https://entegrasyon.asseco-see.com.tr/fim/api',
                'gateway'       => [
                    'production'    => 'https://sanalpos.halkbank.com.tr/fim/est3dgate',
                    'test'          => 'https://entegrasyon.asseco-see.com.tr/fim/est3Dgate'
                ]
            ]
        ],
        'teb'    => [
            'name'  => 'TEB',
            'class' => SanalPos\EstPos::class,
            'urls'  => [
                'production'    => 'https://sanalpos.teb.com.tr/fim/api',
                'test'          => 'https://entegrasyon.asseco-see.com.tr/fim/api',
                'gateway'       => [
                    'production'    => 'https://sanalpos.teb.com.tr/fim/est3Dgate',
                    'test'          => 'https://entegrasyon.asseco-see.com.tr/fim/est3Dgate'
                ]
            ]
        ],
        'ingbank'    => [
            'name'  => 'ING Group',
            'class' => SanalPos\EstPos::class,
            'urls'  => [
                'production'    => 'https://sanalpos.ingbank.com.tr/fim/api',
                'test'          => 'https://entegrasyon.asseco-see.com.tr/fim/api',
                'gateway'       => [
                    'production'    => 'https://sanalpos.ingbank.com.tr/fim/est3Dgate',
                    'test'          => 'https://entegrasyon.asseco-see.com.tr/fim/est3Dgate'
                ]
            ]
        ],
        'isbank'    => [
            'name'  => 'İşbank',
            'class' => SanalPos\EstPos::class,
            'urls'  => [
                'production'    => 'https://sanalpos.isbank.com.tr/fim/api',
                'test'          => 'https://entegrasyon.asseco-see.com.tr/fim/api',
                'gateway'       => [
                    'production'    => 'https://sanalpos.isbank.com.tr/fim/est3Dgate',
                    'test'          => 'https://entegrasyon.asseco-see.com.tr/fim/est3Dgate'
                ]
            ]
        ],
        'hsbc' => [
            'name'  => 'HSBC Bank',
            'class' => SanalPos\EstPos::class,
            'urls'  => [
                'production'    => 'https://vpos.advantage.com.tr/fim/api',
                'test'          => 'https://entegrasyon.asseco-see.com.tr/fim/api',
                'gateway'       => [
                    'production'    => 'https://vpos.advantage.com.tr/fim/est3Dgate',
                    'test'          => 'https://entegrasyon.asseco-see.com.tr/fim/est3Dgate'
                ]
            ]
        ],
        'denizbank' => [
            'name'  => 'DenizBank',
            'class' => SanalPos\EstPos::class,
            'urls'  => [
                'production'    => 'https://denizbank.est.com.tr/fim/api',
                'test'          => 'https://entegrasyon.asseco-see.com.tr/fim/api',
                'gateway'       => [
                    'production'    => 'https://denizbank.est.com.tr/fim/est3Dgate',
                    'test'          => 'https://entegrasyon.asseco-see.com.tr/fim/est3Dgate'
                ]
            ]
        ],
        'isbank-payflex'    => [
            'name'  => 'İşbank - PayFlex',
            'class' => SanalPos\PayFlex::class,
            'urls'  => [
                'production'    => 'https://trx.payflex.com.tr/VposWeb/v3/Vposreq.aspx',
                'test'          => 'https://sanalpos.innova.com.tr/ISBANK_v4/VposWeb/v3/Vposreq.aspx',
                'gateway'       => [
                    'production'    => 'https://mpi.vpos.isbank.com.tr/MPIEnrollment.aspx',
                    'test'          => 'https://sanalpos.innova.com.tr/ISBANK/MpiWeb/Enrollment.aspx'
                ],
            ]
        ],
        'yapikredi' => [
            'name'  => 'Yapıkredi',
            'class' => SanalPos\PosNet::class,
            'urls'  => [
                'production'    => 'https://www.posnet.ykb.com/PosnetWebService/XML',
                'test'          => 'https://setmpos.ykb.com/PosnetWebService/XML',
                'gateway'       => [
                    'production'    => 'https://www.posnet.ykb.com/3DSWebService/YKBPaymentService',
                    'test'          => 'https://setmpos.ykb.com/3DSWebService/YKBPaymentService'
                ],
            ],
			'order' => [
			    'id_total_length' => 24,
				'id_length' => 20,
				'id_3d_prefix' => 'TDSC',
                'id_3d_pay_prefix' => '', //?
                'id_regular_prefix' => '' //?
			]
        ],
        'garanti' => [
            'name'  => 'Garanti',
            'class' => SanalPos\GarantiPos::class,
            'urls'  => [
                'production'    => 'https://sanalposprov.garanti.com.tr/VPServlet',
                'test'          => 'https://sanalposprovtest.garanti.com.tr/VPServlet',
                'gateway'       => [
                    'production'    => 'https://sanalposprov.garanti.com.tr/servlet/gt3dengine',
                    'test'          => 'https://sanalposprovtest.garanti.com.tr/servlet/gt3dengine'
                ]
            ]
        ],
        'kuveytturk' => [
            'name'  => 'Kuveyt Türk',
            'class' => SanalPos\KuveytPos::class,
            'urls'  => [
                'production'    => 'https://boa.kuveytturk.com.tr/sanalposservice/Home/ThreeDModelPayGate',
                'test'          => 'https://boatest.kuveytturk.com.tr/boa.virtualpos.services/Home/ThreeDModelPayGate',
                'gateway'       => [
                    'production'    => 'https://boa.kuveytturk.com.tr/sanalposservice/Home/ThreeDModelPayGate',
                    'provision'    => 'https://boa.kuveytturk.com.tr/sanalposservice/Home/ThreeDModelProvisionGate',
                    'test'          => 'https://boatest.kuveytturk.com.tr/boa.virtualpos.services/Home/ThreeDModelPayGate',
                    'test_provision'  => 'https://boatest.kuveytturk.com.tr/boa.virtualpos.services/Home/ThreeDModelProvisionGate'
                ]
            ]
        ],
        'vakifbank' => [
            'name'  => 'VakıfBank',
            'class' => SanalPos\VakifPos::class,
            'urls'  => [
                'production'    => 'https://onlineodeme.vakifbank.com.tr:4443/VposService/v3/Vposreq.aspx',
                'test'          => 'https://onlineodemetest.vakifbank.com.tr:4443/VposService/v3/Vposreq.aspx',
                'gateway'       => [
                    'production'    => 'https://3dsecure.vakifbank.com.tr:4443/MPIAPI/MPI_Enrollment.aspx',
                    'test'          => 'https://3dsecuretest.vakifbank.com.tr:4443/MPIAPI/MPI_Enrollment.aspx'
                ],
            ]
        ],
        'paynet' => [
            'name'  => 'Paynet',
            'class' => SanalPos\PaynetPos::class
        ]
    ],

];
