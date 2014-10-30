<?php
	namespace Edde2\Security;

	use Latte\Compiler;
	use Latte\MacroNode;
	use Latte\Macros\MacroSet;
	use Latte\PhpWriter;

	class AclSupportMacro extends MacroSet {
		public static function install(Compiler $aCompiler) {
			$self = new self($aCompiler);
			$self->addMacro('acl', array(
				$self,
				'macroAclOpen'
			), array(
				$self,
				'macroAclClose'
			));
		}

		public function macroAclOpen(MacroNode $aNode, PhpWriter $aWriter) {
			return $aWriter->write("if(\$user->acl('%node.word', null, false)) {");
		}

		public function macroAclClose(MacroNode $aNode, PhpWriter $aWriter) {
			return '}';
		}
	}
