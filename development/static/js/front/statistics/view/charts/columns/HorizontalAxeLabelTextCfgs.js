Class.Define('App.view.charts.columns.HorizontalAxeLabelTextCfgs', {
	Static: {
		Data: [{
			MaxChars: 3,
			LabelCfg: {
				fontSize: '13px', textAlign: 'center'
			},
			BaseCfg: { insetPadding: '25 25 25 20' }
		}, {
			MaxChars: 5,
			LabelCfg: {
				fontSize: '13px',
				rotate: { degrees: -22 },
				rotationCenterX: -40, rotationCenterY: -30
			},
			BaseCfg: { insetPadding: '25 25 25 20' }
		}, {
			MaxChars: 10,
			LabelCfg: {
				fontSize: '13px',
				rotate: { degrees: -22 },
				rotationCenterX: -45, rotationCenterY: -35
			},
			BaseCfg: { insetPadding: '25 25 25 20' }
		}, {
			MaxChars: 15,
			LabelCfg: {
				fontSize: '13px',
				rotate: { degrees: -32 },
				rotationCenterX: -50, rotationCenterY: -20
			},
			BaseCfg: { insetPadding: '25 25 25 20' }
		}, {
			MaxChars: 20,
			LabelCfg: {
				rotate: { degrees: -38 },
				rotationCenterX: -65, rotationCenterY: -40
			},
			BaseCfg: { insetPadding: '25 25 25 40' }
		}, {
			MaxChars: 25,
			LabelCfg: {
				rotate: { degrees: -45 },
				rotationCenterX: -70, rotationCenterY: -45
			},
			BaseCfg: { insetPadding: '25 25 25 40' }
		}, {
			MaxChars: 30,
			LabelCfg: {
				rotate: { degrees: -57 },
				rotationCenterX: -85, rotationCenterY: -55
			},
			BaseCfg: { insetPadding: '25 25 25 45' }
		}, {
			MaxChars: 35,
			LabelCfg: {
				rotate: { degrees: -62 },
				rotationCenterX: -90, rotationCenterY: -65
			},
			BaseCfg: { insetPadding: '25 25 25 50' }
		}, {
			MaxChars: Infinity,
			LabelCfg: {
				rotate: { degrees: -90 },
				rotationCenterX: -55, rotationCenterY: -55
			},
			BaseCfg: { insetPadding: '25 25 25 15' }
		}]
	}
});