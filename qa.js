var Question = React.createClass({
	render: function() {
		return(
			React.createElement(
				"li",
				null,
				React.createElement(
					"span",
					{ className: "question-text" },
					undefined.props.question
				),
				React.createElement(
					"span",
					{ className: "question-name" },
					"posted by ",
					$this.props.name
				)
			)
		);
	}
});